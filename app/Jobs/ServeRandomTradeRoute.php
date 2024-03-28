<?php

declare(strict_types=1);

namespace App\Jobs;

use Illuminate\Database\Eloquent\Collection as EloquentCollection;

class ServeRandomTradeRoute extends ServeTradeRoute
{
    protected function getPossibleTradeRoutes(): EloquentCollection
    {
        return $this->possibleTradeRoutes->shuffle();
    }
}
