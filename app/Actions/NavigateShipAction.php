<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\FlightModes;
use App\Helpers\LocationHelper;
use App\Helpers\SpaceTraders;
use App\Models\Ship;
use App\Models\Waypoint;
use Lorisleiva\Actions\Concerns\AsAction;

class NavigateShipAction
{
    use AsAction;

    private SpaceTraders $api;

    public function __construct()
    {
        $this->api = app(SpaceTraders::class);
    }

    public function handle(
        Ship $ship,
        string $destinationWaypointSymbol,
    ): Ship {
        $ship
            ->moveIntoOrbit()
            ->setFlightMode(FlightModes::CRUISE)
            ->update(['destination' => $destinationWaypointSymbol]);

        if (!$ship->fuel_capacity) {
            // todo: adjust to travel across systems
            $this->api
                ->navigateShip($ship->symbol, $destinationWaypointSymbol)
                ->updateShip($ship)
                ->save();

            return $ship;
        }

        $destinationWaypoint = Waypoint::findBySymbol($destinationWaypointSymbol);
        $distanceToDestinationWaypoint = $ship->distanceTo($destinationWaypoint);
        $canReachDestinationWithoutRefueling = $distanceToDestinationWaypoint <= $ship->fuel_current;
        $canRefuelAtCurrenLocation = $ship->canRefuelAtCurrentLocation();

        if ($ship->distanceTo($destinationWaypoint) <= $ship->fuel_capacity) {
            if ($canReachDestinationWithoutRefueling) {
                if ($canRefuelAtCurrenLocation && !$destinationWaypoint->can_refuel) {
                    // refuel at current position to avoid getting stranded at destination
                    $ship->refuel();
                }
                $this->api
                    ->navigateShip($ship->symbol, $destinationWaypointSymbol)
                    ->updateShip($ship)
                    ->save();

                return $ship;
            }

            if ($canRefuelAtCurrenLocation) {
                $ship->refuel();
                $this->api
                    ->navigateShip($ship->symbol, $destinationWaypointSymbol)
                    ->updateShip($ship)
                    ->save();

                return $ship;
            }

            /*
             * cannot reach destination without refueling
             * and cannot refuel at current location
             */
            if ($distanceToDestinationWaypoint > $ship->fuel_current) {
                foreach ([
                    $destinationWaypoint->closestRefuelingWaypoint(),
                    $ship->waypoint->closestRefuelingWaypoint(),
                ] as $closestRefuelingWaypoint) {
                    if (
                        $ship->fuel_current >= $ship->distanceTo($closestRefuelingWaypoint)
                        && $distanceToDestinationWaypoint > $ship->distanceTo($closestRefuelingWaypoint)
                    ) {
                        $this->api
                            ->navigateShip($ship->symbol, $closestRefuelingWaypoint->symbol)
                            ->updateShip($ship)
                            ->save();

                        return $ship;
                    }
                }

                // no other chance than to drift to the destination
                $ship->setFlightMode(FlightModes::DRIFT);
                $this->api
                    ->navigateShip($ship->symbol, $destinationWaypointSymbol)
                    ->updateShip($ship)
                    ->save();
            }

            // when will this code ever be reached?
            throw new \Exception("{$ship->symbol} cannot reach destination {$destinationWaypointSymbol} from {$ship->waypoint_symbol}", 1);
            // $this->api
            //     ->navigateShip($ship->symbol, $destinationWaypointSymbol)
            //     ->updateShip($ship)
            //     ->save();

            // return $ship;
        }

        $routePath = LocationHelper::getRoutePath(
            $ship->waypoint_symbol,
            $destinationWaypointSymbol,
            $ship->fuel_capacity
        );

        if ($routePath) {
            if ($canRefuelAtCurrenLocation) {
                $ship->refuel();
            }
            $this->api
                ->navigateShip($ship->symbol, $routePath[1])
                ->updateShip($ship)
                ->save();

            return $ship;
        }

        $closestRefuelingWaypoint = $ship->waypoint->closestRefuelingWaypoint();

        if (
            ($canRefuelAtCurrenLocation || $ship->fuel_current >= $ship->distanceTo($closestRefuelingWaypoint))
            && $distanceToDestinationWaypoint > $ship->distanceTo($closestRefuelingWaypoint)
            // $closestRefuelingWaypoint must be closer to $destinationWaypoint
            && $distanceToDestinationWaypoint > LocationHelper::distance($closestRefuelingWaypoint, $destinationWaypoint)
        ) {
            if ($canRefuelAtCurrenLocation) {
                $ship->refuel();
            }
            $this->api
                ->navigateShip($ship->symbol, $closestRefuelingWaypoint->symbol)
                ->updateShip($ship)
                ->save();

            return $ship;
        }

        if (!$destinationWaypoint->can_refuel) {
            $closestRefuelingStationToDestination = $destinationWaypoint->closestRefuelingWaypoint();
            $routePathToClosestRefuelingStationToDestination = LocationHelper::getRoutePath(
                $ship->waypoint_symbol,
                $closestRefuelingStationToDestination->symbol,
                $ship->fuel_capacity
            );

            if ($routePathToClosestRefuelingStationToDestination) {
                $ship->refuel();
                $this->api
                    ->navigateShip($ship->symbol, $routePathToClosestRefuelingStationToDestination[1])
                    ->updateShip($ship)
                    ->save();

                return $ship;
            }
        }

        // no route found, no other chance than to drift to the destination
        $ship->setFlightMode(FlightModes::DRIFT);

        if ($canRefuelAtCurrenLocation) {
            $ship->refuel();
        }

        $this->api
            ->navigateShip($ship->symbol, $destinationWaypointSymbol)
            ->updateShip($ship)
            ->save();

        return $ship;
    }
}
