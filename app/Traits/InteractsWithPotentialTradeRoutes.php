<?php

declare(strict_types=1);

namespace App\Traits;

use App\Actions\UpdateOrRemovePotentialTradeRoutesAction;
use App\Enums\SupplyLevels;
use App\Helpers\LocationHelper;
use App\Jobs\ShipJob;
use App\Models\PotentialTradeRoute;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

/**
 * @mixin ShipJob
 * @method EloquentCollection getPossibleTradeRoutes()
 */
trait InteractsWithPotentialTradeRoutes
{
    public const MIN_PROFIT_PER_FLIGHT = 50_000;

    public const MIN_PROFIT = 1;

    /** @var ?EloquentCollection<int, PotentialTradeRoute> */
    protected ?EloquentCollection $possibleTradeRoutes = null;

    protected function initPossibleNewRoutes(): void
    {
        UpdateOrRemovePotentialTradeRoutesAction::run();

        $this->possibleTradeRoutes = PotentialTradeRoute::where([
            ['profit', '>', static::MIN_PROFIT],
            ['profit_per_flight', '>=', static::MIN_PROFIT_PER_FLIGHT],
            ['distance', '<=', $this->ship->fuel_capacity],
            ['supply_at_origin', '<>', SupplyLevels::SCARCE],
            ['supply_at_destination', '<>', SupplyLevels::ABUNDANT],
        ])
            ->get()
            ->filter(fn (PotentialTradeRoute $route) => $this->routeDistanceIsPossible($route));
    }

    protected function chooseNewRoute(): void
    {
        dump("{$this->ship->symbol} choosing new trade route, dissociating old one");
        $this->ship->potentialTradeRoute->ship()->dissociate()->save();

        $possibleRoutes = $this->getPossibleTradeRoutes()->whereNull('ship_id');

        $count = $possibleRoutes->count();

        dump("{$this->ship->symbol} found {$count} possible trade routes");

        if ($count === 0) {
            dump("{$this->ship->symbol} no new trade routes found");

            return;
        }

        /** @var PotentialTradeRoute */
        $newRoute = $possibleRoutes->first();

        if ($newRoute) {
            dump("{$this->ship->symbol} new trade route {$newRoute->origin} -> {$newRoute->destination} with {$newRoute->trade_symbol->value} profit per flight {$newRoute->profit_per_flight}");
        } else {
            dump("{$this->ship->symbol} no unserved trade route found!");
        }

        $newRoute->ship()->associate($this->ship)->save();
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
