<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Enums\SupplyLevels;
use App\Models\PotentialTradeRoute;
use App\Traits\InteractsWithPotentialTradeRoutes;
use Illuminate\Contracts\Queue\ShouldBeUniqueUntilProcessing;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

abstract class ServeTradeRoute extends ShipJob implements ShouldBeUniqueUntilProcessing
{
    use InteractsWithPotentialTradeRoutes;

    /**
     * Get the unique ID for the job.
     */
    public function uniqueId(): string
    {
        return static::class . ':' . $this->shipSymbol;
    }

    /**
     * Execute the job.
     */
    protected function handleShip(): void
    {
        if ($this->ship?->task?->type?->getCorrespondingJob() !== static::class) {
            $this->log('is not executing this task anymore.');

            // remove ship from potential trade route if it's not serving a trade route anymore
            PotentialTradeRoute::getQuery()
                ->where('ship_id', $this->ship->id)
                ->update(['ship_id' => null]);

            return;
        }

        $this->initPossibleNewRoutes();

        $this->log("serving trade route, currently located at {$this->ship->waypoint_symbol}");
        if (!$this->ship->potentialTradeRoute) {
            $this->log('has no route yet, choosing a new one.');
            $this->chooseNewRoute();
            if ($this->ship->refresh()->potentialTradeRoute) {
                $this->handleShip(); // call itself to directly handle the new route
            }

            return;
        }

        $this->log("serving trade route {$this->ship->potentialTradeRoute->origin} -> {$this->ship->potentialTradeRoute->destination} with {$this->ship->potentialTradeRoute->trade_symbol->value} and a profit_per_flight of {$this->ship->potentialTradeRoute->profit_per_flight}");

        if ($this->ship->cargo_is_empty) {
            $this->log('cargo is empty');
            if ($this->ship->waypoint_symbol === $this->ship->potentialTradeRoute->origin) {
                if ($this->routeIsStillPossible()) {
                    $this->log("purchase cargo {$this->ship->potentialTradeRoute->trade_symbol->value}");

                    // while (!$this->ship->refresh()->is_fully_loaded) {
                    $this->ship->purchaseCargo(
                        $this->ship->potentialTradeRoute->trade_symbol,
                        min($this->ship->potentialTradeRoute->trade_volume_at_origin, $this->ship->available_cargo_capacity)
                    );
                    // }

                    if ($this->ship->potentialTradeRoute->supply_at_origin === SupplyLevels::ABUNDANT && !$this->ship->refresh()->is_fully_loaded) {
                        $this->ship->purchaseCargo(
                            $this->ship->potentialTradeRoute->trade_symbol,
                            min($this->ship->potentialTradeRoute->trade_volume_at_origin, $this->ship->available_cargo_capacity)
                        );
                    }

                    $this->log("fly to {$this->ship->potentialTradeRoute->destination}");
                    $this->flyToLocation($this->ship->potentialTradeRoute->destination);
                } else {
                    $this->log('trade route does not exist anymore');

                    $this->chooseNewRoute();

                    if ($this->ship->refresh()->potentialTradeRoute) {
                        $this->handleShip(); // call itself to directly handle the new route
                    }
                }

                return;
            }

            $this->log("fly to {$this->ship->potentialTradeRoute->origin}");
            $this->flyToLocation($this->ship->potentialTradeRoute->origin);

            return;
        }

        $this->log('cargo is not empty');
        if ($this->ship->waypoint_symbol === $this->ship->potentialTradeRoute->destination) {
            $this->log("sell cargo {$this->ship->potentialTradeRoute->trade_symbol->value}");
            while (!$this->ship->refresh()->cargo_is_empty) {
                $cargo = $this->ship->cargos()->firstWhere('symbol', $this->ship->potentialTradeRoute->trade_symbol);
                $this->ship->sellCargo(
                    $this->ship->potentialTradeRoute->trade_symbol,
                    min($this->ship->potentialTradeRoute->trade_volume_at_destination, $cargo->units)
                );
            }

            if ($this->routeIsStillPossible()) {
                $this->log("fly to {$this->ship->potentialTradeRoute->origin}");
                $this->flyToLocation($this->ship->potentialTradeRoute->origin);
            } else {
                $this->log('trade route is not profitable enough');

                $this->chooseNewRoute();

                if ($this->ship->refresh()->potentialTradeRoute) {
                    $this->handleShip(); // call itself to directly handle the new route
                }
            }

            return;
        }
        $this->log("fly to {$this->ship->potentialTradeRoute->destination}");
        $this->flyToLocation($this->ship->potentialTradeRoute->destination);

        return;

        $this->log('did not match any conditions');
    }

    abstract protected function getPossibleTradeRoutes(): EloquentCollection;
}
