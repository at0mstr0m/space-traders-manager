<?php

declare(strict_types=1);

namespace App\Data;

use App\Interfaces\GeneratableFromResponse;
use Illuminate\Support\Arr;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;

class MarketData extends Data implements GeneratableFromResponse
{
    public function __construct(
        public string $symbol,
        #[DataCollectionOf(ImportExportExchangeGoodData::class)]
        public ?DataCollection $exports = null,
        #[DataCollectionOf(ImportExportExchangeGoodData::class)]
        public ?DataCollection $imports = null,
        #[DataCollectionOf(ImportExportExchangeGoodData::class)]
        public ?DataCollection $exchange = null,
        #[DataCollectionOf(MarketTransactionData::class)]
        public ?DataCollection $transactions = null,
        #[DataCollectionOf(TradeGoodsData::class)]
        public ?DataCollection $tradeGoods = null,
    ) {}

    public static function fromResponse(array $response): static
    {
        $waypointSymbol = $response['symbol'];
        foreach (['exports', 'imports', 'exchange'] as $source) {
            ${$source} = static::extractImportExportExchangeGoodData($response, $source, $waypointSymbol);
        }

        return new static(
            symbol: $waypointSymbol,
            exports: ImportExportExchangeGoodData::collectionFromResponse($exports),
            imports: ImportExportExchangeGoodData::collectionFromResponse($imports),
            exchange: ImportExportExchangeGoodData::collectionFromResponse($exchange),
            transactions: MarketTransactionData::collectionFromResponse(data_get($response, 'transactions', [])),
            tradeGoods: TradeGoodsData::collectionFromResponse(data_get($response, 'tradeGoods', [])),
        );
    }

    private static function extractImportExportExchangeGoodData(
        array $response,
        string $source,
        string $waypointSymbol
    ): array {
        return Arr::map(
            $response[$source],
            fn ($item) => [...$item, 'waypointSymbol' => $waypointSymbol]
        );
    }
}
