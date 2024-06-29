<?php

declare(strict_types=1);

namespace App\Actions;

use App\Helpers\LocationHelper;
use App\Helpers\SpaceTraders;
use App\Models\System;
use Illuminate\Support\Collection;
use Lorisleiva\Actions\Concerns\AsAction;

class UpdateSystemConnections
{
    use AsAction;

    public function handle(string $waypointSymbol): void
    {
        /** @var SpaceTraders */
        $api = app(SpaceTraders::class);
        $systemSysmbol = LocationHelper::parseSystemSymbol($waypointSymbol);
        $originSystem = System::findBySymbol($systemSysmbol);
        $connectedSystems = $api->getJumpGate($waypointSymbol)
            ->connections
            ->map(fn (string $connection) => LocationHelper::parseSystemSymbol($connection))
            ->pipe(
                fn (Collection $connections) => System::select('id')
                    ->whereIn('symbol', $connections)
                    ->get()
            );
        $originSystem->connections()->sync($connectedSystems);
    }
}
