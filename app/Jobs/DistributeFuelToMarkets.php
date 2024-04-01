<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Enums\TradeSymbols;
use Illuminate\Contracts\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

class DistributeFuelToMarkets extends ServeTradeRoute
{
    protected function getPossibleTradeRoutes(): EloquentCollection
    {
        return $this->possibleTradeRoutes->sortByDesc('profit');
    }

    /**
     * @override
     */
    protected function buildPossibleNewRoutesQuery(EloquentBuilder $query): EloquentBuilder
    {
        return $query->where([
            ['profit', '>', 0],
            ['trade_symbol', '=', TradeSymbols::FUEL],
        ]);
    }
}
