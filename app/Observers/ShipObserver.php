<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Ship;
use App\Services\Firebase;

class ShipObserver
{
    /**
     * Handle the Ship "updated" event.
     */
    public function updated(Ship $ship): void
    {
        if ($ship->isDirty('task_id')) {
            dispatch(function () use ($ship) {
                /** @var Firebase */
                $firebase = app(Firebase::class);
                $firebase->setShipTaskRelation($ship);
            })->afterResponse();
        }
    }
}
