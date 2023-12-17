<?php

namespace App\Data;

use Spatie\LaravelData\Data;

class PotentialTradeRouteData extends Data
{
    public function __construct(
        public string $symbol,
        public MarketData $exportingMarket,
        public MarketData $importingMarket,
    ) {}
}
