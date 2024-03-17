<?php

declare(strict_types=1);

namespace App\Jobs;

use Illuminate\Contracts\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

class ServeBestTradeRoute extends ServeTradeRoute
{
    protected function getPossibleTradeRoutes(): EloquentCollection
    {
        return $this->possibleTradeRoutes->sortByDesc('profit_per_flight');
    }

    /**
     * @override
     */
    protected function buildPossibleNewRoutesQuery(EloquentBuilder $query): EloquentBuilder
    {
        return $query->where('profit_per_flight', '>=', static::MIN_PROFIT_PER_FLIGHT);
    }
}
