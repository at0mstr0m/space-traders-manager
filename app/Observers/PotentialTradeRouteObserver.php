<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\PotentialTradeRoute;
use App\Services\Firebase;

class PotentialTradeRouteObserver
{
    /**
     * Handle the PotentialTradeRoute "created" event.
     */
    public function created(PotentialTradeRoute $potentialTradeRoute): void
    {
        dispatch(function () use ($potentialTradeRoute) {
            /** @var Firebase */
            $firebase = app(Firebase::class);
            $firebase->uploadPotentialTradeRoute($potentialTradeRoute);
        })->afterResponse();
    }

    /**
     * Handle the PotentialTradeRoute "deleted" event.
     */
    public function deleted(PotentialTradeRoute $potentialTradeRoute): void
    {
        $key = $potentialTradeRoute->fireBaseReference?->key;

        dispatch(function () use ($key) {
            /** @var Firebase */
            $firebase = app(Firebase::class);
            $firebase->deletePotentialTradeRoute($key);
        })->afterResponse();
    }
}
