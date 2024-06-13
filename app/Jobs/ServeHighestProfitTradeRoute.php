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
        return $query->where('profit', '>', 0)
            ->whereIn('supply_at_origin', [SupplyLevels::ABUNDANT, SupplyLevels::HIGH])
            ->whereIn('supply_at_destination', [SupplyLevels::SCARCE, SupplyLevels::LIMITED]);
    }
}
