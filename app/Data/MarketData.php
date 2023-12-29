<?php

declare(strict_types=1);

namespace App\Data;

use App\Interfaces\GeneratableFromResponse;
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
        return new static(
            symbol: $response['symbol'],
            exports: ImportExportExchangeGoodData::collectionFromResponse($response['exports']),
            imports: ImportExportExchangeGoodData::collectionFromResponse($response['imports']),
            exchange: ImportExportExchangeGoodData::collectionFromResponse($response['exchange']),
            transactions: MarketTransactionData::collectionFromResponse(data_get($response, 'transactions', [])),
            tradeGoods: TradeGoodsData::collectionFromResponse(data_get($response, 'tradeGoods', [])),
        );
    }
}
