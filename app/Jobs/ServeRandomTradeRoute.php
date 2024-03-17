<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Enums\SupplyLevels;
use Illuminate\Contracts\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

class ServeRandomTradeRoute extends ServeTradeRoute
{
    protected function getPossibleTradeRoutes(): EloquentCollection
    {
        return $this->possibleTradeRoutes->shuffle();
    }

    /**
     * @override
     */
    protected function buildPossibleNewRoutesQuery(EloquentBuilder $query): EloquentBuilder
    {
        return $query->where(
            fn (EloquentBuilder $query) => $query->where('profit_per_flight', '>=', static::MIN_PROFIT_PER_FLIGHT)
                ->orWhere(
                    fn (EloquentBuilder $query) => $query->where([
                        'supply_at_origin' => SupplyLevels::ABUNDANT,
                        'supply_at_destination' => SupplyLevels::SCARCE,
                    ])
                )
        );
    }
}
