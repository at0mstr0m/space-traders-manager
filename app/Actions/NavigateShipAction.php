<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\FlightModes;
use App\Enums\WaypointTypes;
use App\Exceptions\DestinationUnreachableException;
use App\Helpers\LocationHelper;
use App\Helpers\SpaceTraders;
use App\Models\Ship;
use App\Models\Waypoint;
use Lorisleiva\Actions\Concerns\AsAction;

class NavigateShipAction
{
    use AsAction;

    private SpaceTraders $api;

    private ?Ship $ship = null;

    public function __construct()
    {
        $this->api = app(SpaceTraders::class);
    }

    public function handle(
        Ship $ship,
        string $destinationWaypointSymbol,
    ): Ship {
        $this->ship = $ship;
        $this->ship
            ->moveIntoOrbit()
            ->setFlightMode(FlightModes::CRUISE)
            ->update(['destination' => $destinationWaypointSymbol]);

        $isInSameSystem = LocationHelper::waypointIsInSystem(
            $this->ship->waypoint_symbol,
            LocationHelper::parseSystemSymbol($destinationWaypointSymbol)
        );

        if (!$this->ship->fuel_capacity && $isInSameSystem) {
            // directly fly there
            if ($isInSameSystem) {
                $this->navigateShip($destinationWaypointSymbol);

                return $this->ship;
            }

            // first fly to jump gate in current system
            if ($this->ship->waypoint->type !== WaypointTypes::JUMP_GATE) {
                $this->navigateShip($this->ship->system->jumpGate->symbol);

                return $this->ship;
            }

            $routePath = LocationHelper::getRoutePath(
                $this->ship->waypoint_symbol,
                $destinationWaypointSymbol,
                $this->ship->fuel_capacity
            );

            $this->handlePath($routePath);
        }

        $destinationWaypoint = Waypoint::findBySymbol($destinationWaypointSymbol);
        $distanceToDestinationWaypoint = $this->ship->distanceTo($destinationWaypoint);
        $canReachDestinationWithoutRefueling = $distanceToDestinationWaypoint <= $this->ship->fuel_current;
        $canRefuelAtCurrenLocation = $this->ship->can_refuel_at_current_location;

        if (
            $distanceToDestinationWaypoint  // if 0, not in same system
            && $this->ship->distanceTo($destinationWaypoint) <= $this->ship->fuel_capacity
        ) {
            if ($canReachDestinationWithoutRefueling || $canRefuelAtCurrenLocation) {
                $this->navigateShip($destinationWaypointSymbol);

                return $this->ship;
            }

            /*
             * cannot reach destination without refueling
             * and cannot refuel at current location
             */
            if ($distanceToDestinationWaypoint > $this->ship->fuel_current) {
                foreach ([
                    $destinationWaypoint->closestRefuelingWaypoint(),
                    $this->ship->waypoint->closestRefuelingWaypoint(),
                ] as $closestRefuelingWaypoint) {
                    if (
                        $this->ship->fuel_current >= $this->ship->distanceTo($closestRefuelingWaypoint)
                        && $distanceToDestinationWaypoint > $this->ship->distanceTo($closestRefuelingWaypoint)
                    ) {
                        $this->navigateShip($closestRefuelingWaypoint->symbol);

                        return $this->ship;
                    }
                }

                // no other chance than to drift to the destination
                $this->ship->setFlightMode(FlightModes::DRIFT);
                $this->navigateShip($destinationWaypointSymbol);
            }

            // when will this code ever be reached?
            throw new \Exception("{$this->ship->symbol} cannot reach destination {$destinationWaypointSymbol} from {$this->ship->waypoint_symbol}", 1);
            // $this->navigateShip($destinationWaypointSymbol);
            // return $this->ship;
        }

        $routePath = LocationHelper::getRoutePath(
            $this->ship->waypoint_symbol,
            $destinationWaypointSymbol,
            $this->ship->fuel_capacity
        );

        if ($routePath) {
            return $this->handlePath($routePath);
        }
        if ($routePath === false) {
            throw new DestinationUnreachableException($this->ship, $destinationWaypointSymbol);
        }

        $closestRefuelingWaypoint = $this->ship->waypoint->closestRefuelingWaypoint();
        $distanceBetweenClosestRefuelingWaypointAndDestination = LocationHelper::distance(
            $closestRefuelingWaypoint,
            $destinationWaypoint
        );

        if ($distanceToDestinationWaypoint > $distanceBetweenClosestRefuelingWaypointAndDestination) {
            if ($this->ship->distanceTo($closestRefuelingWaypoint) >= $this->ship->fuel_current) {
                // no other chance than to drift to the closest refueling waypoint
                $this->ship->setFlightMode(FlightModes::DRIFT);
            }

            $this->navigateShip($closestRefuelingWaypoint->symbol);

            return $this->ship;
        }

        if (!$destinationWaypoint->can_refuel) {
            $closestRefuelingStationToDestination = $destinationWaypoint->closestRefuelingWaypoint();
            $routePathToClosestRefuelingStationToDestination = LocationHelper::getRoutePath(
                $this->ship->waypoint_symbol,
                $closestRefuelingStationToDestination->symbol,
                $this->ship->fuel_capacity
            );

            if ($routePathToClosestRefuelingStationToDestination) {
                return $this->handlePath(
                    $routePathToClosestRefuelingStationToDestination
                );
            }
        }

        // no route found, no other chance than to drift to the destination
        $this->ship->setFlightMode(FlightModes::DRIFT);

        $this->navigateShip($destinationWaypointSymbol);

        return $this->ship;
    }

    protected function handlePath(array $path): Ship
    {
        $nextStep = $path[1];
        if (
            $this->ship->waypoint->type === WaypointTypes::JUMP_GATE
            && $this->ship->waypoint->system_symbol !== LocationHelper::parseSystemSymbol($nextStep)
            && Waypoint::findBySymbol($nextStep)->type === WaypointTypes::JUMP_GATE
        ) {
            $this->navigateShip($nextStep, true);

            return $this->ship;
        }
        $this->navigateShip($nextStep);

        return $this->ship;
    }

    protected function navigateShip(
        string $destinationWaypointSymbol,
        bool $jump = false
    ): void {
        if ($this->ship->can_refuel_at_current_location) {
            $this->ship->refuel();
        }

        $this->api
            ->{$jump ? 'jumpShip' : 'navigateShip'}(
                $this->ship->symbol,
                $destinationWaypointSymbol
            )
            ->updateShip($this->ship)
            ->save();
    }
}
