<?php

declare(strict_types=1);

namespace Tests\Feature\App\Actions;

use App\Actions\NavigateShipAction;
use App\Enums\FlightModes;
use App\Enums\WaypointTypes;
use App\Exceptions\DestinationUnreachableException;
use App\Helpers\SpaceTraders;
use App\Models\Ship;
use App\Models\System;
use App\Models\Waypoint;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Support\Facades\Http;
use Mockery\LegacyMockInterface;
use Mockery\MockInterface;

uses()->beforeEach(fn () => Http::fake());

function expectNavigationTo(
    Ship $ship,
    string $destinationSymbol,
    ?string $nextNavigationStep = null,
): void {
    /** @var LegacyMockInterface|NavigateShipAction */
    $action = NavigateShipAction::partialMock()
        ->shouldAllowMockingProtectedMethods();
    $action->shouldReceive('navigateShip')
        ->with($nextNavigationStep ?? $destinationSymbol)
        ->once();
    expect($action->handle($ship, $destinationSymbol))->toBe($ship);
}

test('API Helper SpaceTraders can be mocked', function () {
    $this->instance(
        SpaceTraders::class,
        \Mockery::mock(SpaceTraders::class, function (MockInterface $mock) {
            $mock->shouldReceive('getStatus')
                ->once()
                ->andReturn(collect());
        })
    );

    app(SpaceTraders::class)->getStatus();
});

it('navigates to destination', function (
    int $fuelCapacity,
    Waypoint $currentWaypoint,
    string $destination,
    ?string $nextNavigationStep = null,
    ?\Closure $manipulateMock = null,
) {
    $ship = \Mockery::mock(
        Ship::factory()
            ->fullyFueled($fuelCapacity)
            ->atWaypoint($currentWaypoint)
            ->makeOne()
    );
    $ship->shouldReceive('moveIntoOrbit', 'setFlightMode')
        ->andReturnSelf();
    $ship->shouldReceive('update')
        ->with(['destination' => $destination])
        ->andReturnTrue();

    if ($manipulateMock) {
        $manipulateMock($ship);
    }

    expectNavigationTo($ship, $destination, $nextNavigationStep);
})->with([
    // same system
    // no fuel_capacity
    function () {
        $waypoints = Waypoint::factory(5)
            ->inSystem()
            ->canRefuel()
            ->state(new Sequence(
                ['x' => 1, 'y' => 1],
                ['x' => 1000, 'y' => 1000],
            ))
            ->create();

        return [
            'fuelCapacity' => 0,
            'currentWaypoint' => $waypoints->get(0),
            'destination' => $waypoints->get(1)->symbol,
        ];
    },
    // same system
    // reachable without refueling
    // can refuel at all locations
    function () {
        $waypoints = Waypoint::factory(5)
            ->inSystem()
            ->canRefuel()
            ->state(new Sequence(
                ['x' => -100, 'y' => -100],
                ['x' => -50, 'y' => -50],
                ['x' => 1, 'y' => 1],
                ['x' => 50, 'y' => 50],
                ['x' => 100, 'y' => 100],
            ))
            ->create();

        return [
            'fuelCapacity' => 200,
            'currentWaypoint' => $waypoints->get(1),
            'destination' => $waypoints->get(3)->symbol,
        ];
    },
    // same system
    // next refueling station reachable without refueling
    // cannot refuel at current location
    function () {
        $system = System::factory()->create();
        $waypoints = Waypoint::factory(2)
            ->inSystem($system)
            ->canRefuel()
            ->state(new Sequence(
                ['x' => 1, 'y' => 1],
                ['x' => 100, 'y' => 100],
            ))
            ->create();
        $currentWaypoint = Waypoint::factory()
            ->inSystem($system)
            ->createOne(['x' => -50, 'y' => -50]);

        return [
            'fuelCapacity' => 200,
            'currentWaypoint' => $currentWaypoint,
            'destination' => $waypoints->get(1)->symbol,
            'nextNavigationStep' => $waypoints->get(0)->symbol,
        ];
    },
    // same system
    // next refueling station reachable without refueling
    // cannot refuel at current location and destination
    function () {
        $system = System::factory()->create();
        $waypointsToRefuel = Waypoint::factory(2)
            ->inSystem($system)
            ->canRefuel()
            ->state(new Sequence(
                ['x' => 1, 'y' => 1],
                ['x' => 100, 'y' => 100],
            ))
            ->create();
        $waypointsThatCannotRefuel = Waypoint::factory(2)
            ->inSystem($system)
            ->state(new Sequence(
                ['x' => -100, 'y' => -100],
                ['x' => 200, 'y' => 200],
            ))
            ->create();

        return [
            'fuelCapacity' => 200,
            'currentWaypoint' => $waypointsThatCannotRefuel->get(0),
            'destination' => $waypointsThatCannotRefuel->get(1)->symbol,
            'nextNavigationStep' => $waypointsToRefuel->get(0)->symbol,
        ];
    },
    // same system
    // all can refuel
    // destination not reachable without refueling in the middle
    function () {
        $system = System::factory()->create();
        $waypoints = Waypoint::factory(3)
            ->inSystem($system)
            ->canRefuel()
            ->state(new Sequence(
                ['x' => 1, 'y' => 1],
                ['x' => 100, 'y' => 100],
                ['x' => 200, 'y' => 200],
            ))
            ->create();

        return [
            'fuelCapacity' => 200,
            'currentWaypoint' => $waypoints->get(0),
            'destination' => $waypoints->get(2)->symbol,
            'nextNavigationStep' => $waypoints->get(1)->symbol,
        ];
    },
    // same system
    // destination cannot refuel
    // destination not reachable without refueling in the middle
    function () {
        $system = System::factory()->create();
        $waypoints = Waypoint::factory(2)
            ->inSystem($system)
            ->canRefuel()
            ->state(new Sequence(
                ['x' => 1, 'y' => 1],
                ['x' => 100, 'y' => 100],
            ))
            ->create();
        $destination = Waypoint::factory()
            ->inSystem($system)
            ->createOne(['x' => 200, 'y' => 200])
            ->symbol;

        return [
            'fuelCapacity' => 200,
            'currentWaypoint' => $waypoints->get(0),
            'destination' => $destination,
            'nextNavigationStep' => $waypoints->get(1)->symbol,
        ];
    },
    // same system
    // destination can refuel but current location cannot
    // destination not reachable without refueling in the middle
    function () {
        $system = System::factory()->create();
        $waypoints = Waypoint::factory(2)
            ->inSystem($system)
            ->canRefuel()
            ->state(new Sequence(
                ['x' => 100, 'y' => 100],
                ['x' => 200, 'y' => 200],
            ))
            ->create();
        $currentWaypoint = Waypoint::factory()
            ->inSystem($system)
            ->createOne(['x' => 1, 'y' => 1]);

        return [
            'fuelCapacity' => 200,
            'currentWaypoint' => $currentWaypoint,
            'destination' => $waypoints->get(1)->symbol,
            'nextNavigationStep' => $waypoints->get(0)->symbol,
        ];
    },
    // different but connected systems
    // all can refuel
    // destination not reachable without using a jump gate
    function () {
        $systems = System::factory(2)->create();
        $systems->get(0)->connections()->attach($systems->get(1));
        $systems->get(1)->connections()->attach($systems->get(0));
        $waypoints = Waypoint::factory(2)
            ->inSystem($systems->get(0))
            ->canRefuel()
            ->state(new Sequence(
                ['x' => 1, 'y' => 1, 'type' => WaypointTypes::PLANET],
                ['x' => 100, 'y' => 100, 'type' => WaypointTypes::JUMP_GATE],
            ))
            ->create()
            ->concat(
                Waypoint::factory(2)
                    ->inSystem($systems->get(1))
                    ->canRefuel()
                    ->state(new Sequence(
                        ['x' => 100, 'y' => 100, 'type' => WaypointTypes::JUMP_GATE],
                        ['x' => 1, 'y' => 1, 'type' => WaypointTypes::PLANET],
                    ))
                    ->create()
            );

        return [
            'fuelCapacity' => 200,
            'currentWaypoint' => $waypoints->get(0),
            'destination' => $waypoints->get(3)->symbol,
            'nextNavigationStep' => $waypoints->get(1)->symbol,
        ];
    },
    // different but connected systems
    // destination not reachable without using a jump gate
    // destination in another system cannot refuel
    function () {
        $systems = System::factory(2)->create();
        $systems->get(0)->connections()->attach($systems->get(1));
        $systems->get(1)->connections()->attach($systems->get(0));
        $waypoints = Waypoint::factory(2)
            ->inSystem($systems->get(0))
            ->canRefuel()
            ->state(new Sequence(
                ['x' => 1, 'y' => 1, 'type' => WaypointTypes::PLANET],
                ['x' => 100, 'y' => 100, 'type' => WaypointTypes::JUMP_GATE],
            ))
            ->create()
            ->concat(
                Waypoint::factory(2)
                    ->inSystem($systems->get(1))
                    ->canRefuel()
                    ->state(new Sequence(
                        ['x' => 100, 'y' => 100, 'type' => WaypointTypes::JUMP_GATE],
                        ['x' => 1, 'y' => 1, 'type' => WaypointTypes::PLANET],
                    ))
                    ->create()
            );
        $destination = Waypoint::factory()
            ->inSystem($systems->get(1))
            ->createOne(['x' => -100, 'y' => -100, 'type' => WaypointTypes::ASTEROID])
            ->symbol;

        return [
            'fuelCapacity' => 200,
            'currentWaypoint' => $waypoints->get(0),
            'destination' => $destination,
            'nextNavigationStep' => $waypoints->get(1)->symbol,
        ];
    },
    // different but connected systems
    // destination not reachable without using a jump gate
    // destination in another system cannot refuel
    // can only reach destination by drifting the last step
    function () {
        $systems = System::factory(2)->create();
        $systems->get(0)->connections()->attach($systems->get(1));
        $systems->get(1)->connections()->attach($systems->get(0));
        $waypoints = Waypoint::factory(2)
            ->inSystem($systems->get(0))
            ->canRefuel()
            ->state(new Sequence(
                ['x' => 1, 'y' => 1, 'type' => WaypointTypes::PLANET],
                ['x' => 100, 'y' => 100, 'type' => WaypointTypes::JUMP_GATE],
            ))
            ->create()
            ->concat(
                Waypoint::factory(2)
                    ->inSystem($systems->get(1))
                    ->canRefuel()
                    ->state(new Sequence(
                        ['x' => 100, 'y' => 100, 'type' => WaypointTypes::JUMP_GATE],
                        ['x' => 1, 'y' => 1, 'type' => WaypointTypes::PLANET],
                    ))
                    ->create()
            );
        $destination = Waypoint::factory()
            ->inSystem($systems->get(1))
            ->createOne(['x' => -500, 'y' => -500, 'type' => WaypointTypes::ASTEROID])
            ->symbol;

        return [
            'fuelCapacity' => 200,
            'currentWaypoint' => $waypoints->get(0),
            'destination' => $destination,
            'nextNavigationStep' => $waypoints->get(1)->symbol,
        ];
    },
]);

it('navigates using Jump Gate', function (
    int $fuelCapacity,
    Waypoint $currentWaypoint,
    string $destination,
    ?string $nextNavigationStep = null,
    ?\Closure $manipulateMock = null,
) {
    $ship = \Mockery::mock(
        Ship::factory()
            ->fullyFueled($fuelCapacity)
            ->atWaypoint($currentWaypoint)
            ->makeOne()
    );
    $ship->shouldReceive('moveIntoOrbit', 'setFlightMode')
        ->andReturnSelf();
    $ship->shouldReceive('update')
        ->with(['destination' => $destination])
        ->andReturnTrue();

    if ($manipulateMock) {
        $manipulateMock($ship);
    }

    /** @var LegacyMockInterface|NavigateShipAction */
    $action = NavigateShipAction::partialMock()
        ->shouldAllowMockingProtectedMethods();
    $action->shouldReceive('navigateShip')
        ->with($nextNavigationStep ?? $destination, true)
        ->once();
    expect($action->handle($ship, $destination))->toBe($ship);
})->with([
    // different but connected systems
    // all can refuel
    // destination not reachable without using a jump gate
    // currently at Jump Gate
    function () {
        $systems = System::factory(2)->create();
        $systems->get(0)->connections()->attach($systems->get(1));
        $systems->get(1)->connections()->attach($systems->get(0));
        $waypoints = Waypoint::factory(2)
            ->inSystem($systems->get(0))
            ->canRefuel()
            ->state(new Sequence(
                ['x' => 1, 'y' => 1, 'type' => WaypointTypes::PLANET],
                ['x' => 100, 'y' => 100, 'type' => WaypointTypes::JUMP_GATE],
            ))
            ->create()
            ->concat(
                Waypoint::factory(2)
                    ->inSystem($systems->get(1))
                    ->canRefuel()
                    ->state(new Sequence(
                        ['x' => 100, 'y' => 100, 'type' => WaypointTypes::JUMP_GATE],
                        ['x' => 1, 'y' => 1, 'type' => WaypointTypes::PLANET],
                    ))
                    ->create()
            );

        return [
            'fuelCapacity' => 200,
            'currentWaypoint' => $waypoints->get(1),
            'destination' => $waypoints->get(3)->symbol,
            'nextNavigationStep' => $waypoints->get(2)->symbol,
        ];
    },
    // different but connected systems
    // destination cannot refuel
    // destination not reachable without using a jump gate
    // currently at Jump Gate
    function () {
        $systems = System::factory(2)->create();
        $systems->get(0)->connections()->attach($systems->get(1));
        $systems->get(1)->connections()->attach($systems->get(0));
        $waypoints = Waypoint::factory(2)
            ->inSystem($systems->get(0))
            ->canRefuel()
            ->state(new Sequence(
                ['x' => 1, 'y' => 1, 'type' => WaypointTypes::PLANET],
                ['x' => 100, 'y' => 100, 'type' => WaypointTypes::JUMP_GATE],
            ))
            ->create()
            ->concat(
                Waypoint::factory(2)
                    ->inSystem($systems->get(1))
                    ->canRefuel()
                    ->state(new Sequence(
                        ['x' => 100, 'y' => 100, 'type' => WaypointTypes::JUMP_GATE],
                        ['x' => 1, 'y' => 1, 'type' => WaypointTypes::PLANET],
                    ))
                    ->create()
            );

        $destination = Waypoint::factory()
            ->inSystem($systems->get(1))
            ->createOne(['x' => 200, 'y' => 200])
            ->symbol;

        return [
            'fuelCapacity' => 200,
            'currentWaypoint' => $waypoints->get(1),
            'destination' => $destination,
            'nextNavigationStep' => $waypoints->get(2)->symbol,
        ];
    },
    // different but connected systems
    // destination not reachable without using a jump gate
    // destination is jump gate in other system
    function () {
        $systems = System::factory(2)->create();
        $systems->get(0)->connections()->attach($systems->get(1));
        $systems->get(1)->connections()->attach($systems->get(0));
        $currentWaypoint = Waypoint::factory()
            ->inSystem($systems->get(0))
            ->canRefuel()
            ->createOne(['x' => 100, 'y' => 100, 'type' => WaypointTypes::JUMP_GATE]);
        $destination = Waypoint::factory()
            ->inSystem($systems->get(1))
            ->canRefuel()
            ->createOne(['x' => -100, 'y' => -100, 'type' => WaypointTypes::JUMP_GATE])
            ->symbol;

        return [
            'fuelCapacity' => 200,
            'currentWaypoint' => $currentWaypoint,
            'destination' => $destination,
            'nextNavigationStep' => $destination,
        ];
    },
]);

it('drifts to a refuelling station in the middle to destination', function (
    array $shipAttributes,
    Waypoint $currentWaypoint,
    string $destination,
    ?string $nextNavigationStep = null,
    ?\Closure $manipulateMock = null,
) {
    $ship = \Mockery::mock(
        Ship::factory()
            ->atWaypoint($currentWaypoint)
            ->makeOne($shipAttributes)
    );
    $ship->shouldReceive('moveIntoOrbit', 'setFlightMode')
        ->andReturnSelf();
    $ship->shouldReceive('update')
        ->with(['destination' => $destination])
        ->andReturnTrue();
    $ship->shouldReceive('setFlightMode')
        ->with(FlightModes::DRIFT)
        ->andReturnTrue();

    if ($manipulateMock) {
        $manipulateMock($ship);
    }

    expectNavigationTo($ship, $destination, $nextNavigationStep);
})->with([
    // same system
    // current waypoint and destination cannot refuel
    // refuelling station in the middle
    function () {
        $system = System::factory()->create();
        $waypoints = Waypoint::factory(2)
            ->inSystem($system)
            ->state(new Sequence(
                ['x' => 1, 'y' => 1],
                ['x' => 200, 'y' => 200],
            ))
            ->create();
        $refuellintInTheMiddle = Waypoint::factory()
            ->inSystem($system)
            ->canRefuel()
            ->createOne(['x' => 100, 'y' => 100]);

        return [
            'shipAttributes' => [
                'fuel_capacity' => 200, // not enough to reach destination
                'fuel_consumed' => 200,
                'fuel_current' => 0,    // empty tank
            ],
            'currentWaypoint' => $waypoints->get(0),
            'destination' => $waypoints->get(1)->symbol,
            'nextNavigationStep' => $refuellintInTheMiddle->symbol,
        ];
    },
]);

it('navigates to a refuelling station in the middle to destination', function (
    array $shipAttributes,
    Waypoint $currentWaypoint,
    string $destination,
    ?string $nextNavigationStep = null,
    ?\Closure $manipulateMock = null,
) {
    $ship = \Mockery::mock(
        Ship::factory()
            ->atWaypoint($currentWaypoint)
            ->makeOne($shipAttributes)
    );
    $ship->shouldReceive('moveIntoOrbit', 'setFlightMode')
        ->andReturnSelf();
    $ship->shouldReceive('update')
        ->with(['destination' => $destination])
        ->andReturnTrue();

    if ($manipulateMock) {
        $manipulateMock($ship);
    }

    expectNavigationTo($ship, $destination, $nextNavigationStep);
})->with([
    // same system
    // current waypoint and destination cannot refuel
    // refuelling station in the middle
    function () {
        $system = System::factory()->create();
        $waypoints = Waypoint::factory(2)
            ->inSystem($system)
            ->state(new Sequence(
                ['x' => 1, 'y' => 1],
                ['x' => 200, 'y' => 200],
            ))
            ->create();
        $refuellintInTheMiddle = Waypoint::factory()
            ->inSystem($system)
            ->canRefuel()
            ->createOne(['x' => 50, 'y' => 50]);

        return [
            'shipAttributes' => [
                'fuel_capacity' => 200, // not enough to reach destination
                'fuel_consumed' => 100,
                'fuel_current' => 100,    // empty tank
            ],
            'currentWaypoint' => $waypoints->get(0),
            'destination' => $waypoints->get(1)->symbol,
            'nextNavigationStep' => $refuellintInTheMiddle->symbol,
        ];
    },
]);

it('cannot navigate between systems that are not connected', function (
    array $shipAttributes,
    Waypoint $currentWaypoint,
    string $destination,
    ?string $nextNavigationStep = null,
    ?\Closure $manipulateMock = null,
) {
    $ship = \Mockery::mock(
        Ship::factory()
            ->atWaypoint($currentWaypoint)
            ->makeOne($shipAttributes)
    );
    $ship->shouldReceive('moveIntoOrbit', 'setFlightMode')
        ->andReturnSelf();
    $ship->shouldReceive('update')
        ->with(['destination' => $destination])
        ->andReturnTrue();

    if ($manipulateMock) {
        $manipulateMock($ship);
    }

    NavigateShipAction::run($ship, $nextNavigationStep);
})->with([
    function () {
        $waypoints = Waypoint::factory(2)->create();

        return [
            'shipAttributes' => [
                'fuel_capacity' => 200,
                'fuel_consumed' => 0,
                'fuel_current' => 200,
            ],
            'currentWaypoint' => $waypoints->get(0),
            'destination' => $waypoints->get(1)->symbol,
            'nextNavigationStep' => $waypoints->get(1)->symbol,
        ];
    },
    function () {
        $waypoints = Waypoint::factory(2)->create();

        return [
            'shipAttributes' => [
                'fuel_capacity' => 200,
                'fuel_consumed' => 100,
                'fuel_current' => 100,
            ],
            'currentWaypoint' => $waypoints->get(0),
            'destination' => $waypoints->get(1)->symbol,
            'nextNavigationStep' => $waypoints->get(1)->symbol,
        ];
    },
    function () {
        $waypoints = Waypoint::factory(2)->create();

        return [
            'shipAttributes' => [
                'fuel_capacity' => 200,
                'fuel_consumed' => 200,
                'fuel_current' => 0,
            ],
            'currentWaypoint' => $waypoints->get(0),
            'destination' => $waypoints->get(1)->symbol,
            'nextNavigationStep' => $waypoints->get(1)->symbol,
        ];
    },
])->throws(DestinationUnreachableException::class);
