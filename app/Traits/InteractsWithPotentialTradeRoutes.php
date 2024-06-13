<?php

declare(strict_types=1);

namespace App\Traits;

use App\Actions\UpdateOrRemovePotentialTradeRoutesAction;
use App\Enums\SupplyLevels;
use App\Helpers\LocationHelper;
use App\Jobs\Firebase\UploladPotentialTradeRouteJob;
use App\Jobs\ShipJob;
use App\Models\PotentialTradeRoute;
use Illuminate\Contracts\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

/**
 * @mixin ShipJob
 *
 * @method EloquentCollection getPossibleTradeRoutes()
 */
trait InteractsWithPotentialTradeRoutes
{
    public const MIN_PROFIT = 0.5;

    public const MIN_PROFIT_PER_FLIGHT = 2_000;

    /** @var ?EloquentCollection<int, PotentialTradeRoute> */
    protected ?EloquentCollection $possibleTradeRoutes = null;

    protected function initPossibleNewRoutes(): void
    {
        UpdateOrRemovePotentialTradeRoutesAction::run();

        $this->possibleTradeRoutes = $this->buildPossibleNewRoutesQuery(
            PotentialTradeRoute::where([
                ['supply_at_origin', '<>', SupplyLevels::SCARCE],
                ['supply_at_destination', '<>', SupplyLevels::ABUNDANT],
            ])
        )->get();
        // ->filter(fn (PotentialTradeRoute $route) => $this->routeDistanceIsPossible($route));

        $this->log("found {$this->possibleTradeRoutes->count()} possible trade routes");
    }

    // override this method in child classes to specify query conditions
    protected function buildPossibleNewRoutesQuery(EloquentBuilder $query): EloquentBuilder
    {
        return $query->where([
            ['profit', '>', static::MIN_PROFIT],
            ['profit_per_flight', '>', static::MIN_PROFIT_PER_FLIGHT],
        ]);
    }

    protected function chooseNewRoute(): void
    {
        $this->log('choosing new trade route, dissociating old one');
        if ($this->ship->potentialTradeRoute) {
            $oldRoute = $this->ship->potentialTradeRoute;
            $this->ship->potentialTradeRoute->ship()->dissociate()->save();
            UploladPotentialTradeRouteJob::dispatch($oldRoute->id)
                ->afterResponse();
        }

        $possibleRoutes = $this->getPossibleTradeRoutes()->whereNull('ship_id');

        $count = $possibleRoutes->count();

        $this->log("found {$count} possible trade routes");

        if ($count === 0) {
            $this->log('no new trade routes found');

            return;
        }

        /** @var PotentialTradeRoute */
        $newRoute = $possibleRoutes->first();

        if ($newRoute) {
            $this->log("new trade route {$newRoute->origin} -> {$newRoute->destination} with {$newRoute->trade_symbol->value} profit per flight {$newRoute->profit_per_flight}");
        } else {
            $this->log('no unserved trade route found!');
        }

        $newRoute->ship()->associate($this->ship)->save();
        UploladPotentialTradeRouteJob::dispatch($newRoute->id)
            ->afterResponse();
    }

    protected function routeIsStillPossible(): bool
    {
        $possibleRoutes = $this->getPossibleTradeRoutes();

        return $possibleRoutes->isNotEmpty()
            && $possibleRoutes->contains(
                fn (PotentialTradeRoute $route) => $route->id === $this->ship->potentialTradeRoute->id
            );
    }

    private function routeDistanceIsPossible(PotentialTradeRoute $route): bool
    {
        $maxDistance = $this->ship->fuel_capacity;

        return LocationHelper::distance($route->origin, $this->ship->waypoint_symbol) <= $maxDistance
            && $route->distance <= $maxDistance;
    }
}
