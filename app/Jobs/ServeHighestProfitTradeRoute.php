<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Enums\SupplyLevels;
use Illuminate\Contracts\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

class ServeHighestProfitTradeRoute extends ServeTradeRoute
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
            ['profit_per_flight', '>', 2000],
        ])
            ->whereNotIn('supply_at_destination', [SupplyLevels::ABUNDANT, SupplyLevels::HIGH])
            ->whereNotIn('supply_at_origin', [SupplyLevels::SCARCE, SupplyLevels::LIMITED]);
    }
}
