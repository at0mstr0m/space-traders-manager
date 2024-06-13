<?php

declare(strict_types=1);

namespace App\Observers;

use App\Jobs\Firebase\SetShipRelationJob;
use App\Models\Ship;

class ShipObserver
{
    /**
     * Handle the Ship "updated" event.
     */
    public function updated(Ship $ship): void
    {
        if ($ship->isDirty('task_id')) {
            SetShipRelationJob::dispatch($ship->id)->afterResponse();
        }
    }
}
