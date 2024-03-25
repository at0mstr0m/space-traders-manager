<?php

declare(strict_types=1);

namespace App\Data;

use Illuminate\Support\Collection;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Data;

class MarketData extends Data
{
    /**
     * @param Collection<int, ImportExportExchangeGoodData> $exports
     * @param Collection<int, ImportExportExchangeGoodData> $imports
     * @param Collection<int, ImportExportExchangeGoodData> $exchange
     * @param Collection<int, MarketTransactionData> $transactions
     * @param Collection<int, TradeGoodsData> $tradeGoods
     */
    public function __construct(
        #[MapInputName('symbol')]
        public string $symbol,
        #[MapInputName('exports')]
        public Collection $exports,
        #[MapInputName('imports')]
        public Collection $imports,
        #[MapInputName('exchange')]
        public Collection $exchange,
        #[MapInputName('transactions')]
        public ?Collection $transactions = null,
        #[MapInputName('tradeGoods')]
        public ?Collection $tradeGoods = null,
    ) {
        foreach (['exports', 'imports', 'exchange'] as $attribute) {
            $this->{$attribute} = $this->{$attribute}->transform(
                fn (ImportExportExchangeGoodData $item) => $item->setWaypointSymbol($this->symbol)
            );
        }
    }
}
