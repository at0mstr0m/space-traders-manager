<?php

declare(strict_types=1);

namespace App\Observers;

use App\Jobs\Firebase\DeletePotentialTradeRouteJob;
use App\Jobs\Firebase\UploladPotentialTradeRouteJob;
use App\Models\PotentialTradeRoute;

class PotentialTradeRouteObserver
{
    /**
     * Handle the PotentialTradeRoute "created" event.
     */
    public function created(PotentialTradeRoute $potentialTradeRoute): void
    {
        UploladPotentialTradeRouteJob::dispatch($potentialTradeRoute->id)
            ->afterResponse();
    }

    /**
     * Handle the PotentialTradeRoute "deleted" event.
     */
    public function deleted(PotentialTradeRoute $potentialTradeRoute): void
    {
        $key = $potentialTradeRoute->fireBaseReference?->key;

        DeletePotentialTradeRouteJob::dispatch($key)->afterResponse();
    }
}
