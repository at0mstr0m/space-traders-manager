<?php

declare(strict_types=1);

namespace Tests\Feature\App\Actions;

use App\Actions\NavigateShipAction;
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

        return [
            'fuelCapacity' => 200,
            'currentWaypoint' => Waypoint::factory()
                ->inSystem($system)
                ->createOne(['x' => -50, 'y' => -50]),
            'destination' => $waypoints->get(1)->symbol,
            'nextNavigationStep' => $waypoints->get(0)->symbol,
        ];
    },
]);
