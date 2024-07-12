<?php

declare(strict_types=1);

namespace Tests\Unit\App\Helpers;

use App\Enums\WaypointTypes;
use App\Helpers\LocationHelper;
use App\Models\System;
use App\Models\Waypoint;
use Illuminate\Database\Eloquent\Factories\Sequence;

test('distance is 1 if waypoints have same coordinates', function (
    string|Waypoint $waypoint1,
    string|Waypoint $waypoint2
) {
    expect(LocationHelper::distance($waypoint1, $waypoint2))
        ->toBe(1);
})->with([
    fn () => Waypoint::factory(2)
        ->inSystem()
        ->create(['x' => 1, 'y' => 1])
        ->all(),
    function () {
        $waypoint = Waypoint::factory()->create(['x' => 1, 'y' => 1]);

        return [
            $waypoint,
            $waypoint,
        ];
    },
    fn () => Waypoint::factory(2)
        ->inSystem()
        ->create(['x' => 1, 'y' => 1])
        ->pluck('symbol')
        ->all(),
]);

test('distance is 0 if waypoints ara not in the same system', function (
    string|Waypoint $waypoint1,
    string|Waypoint $waypoint2
) {
    expect(LocationHelper::distance($waypoint1, $waypoint2))
        ->toBe(0);
})->with([
    fn () => Waypoint::factory(2)
        ->create(['x' => 1, 'y' => 1])
        ->all(),
    fn () => Waypoint::factory(2)
        ->create(['x' => 1, 'y' => 1])
        ->pluck('symbol')
        ->all(),
]);

test('getRoutePath returns null', function (
    Waypoint $origin,
    Waypoint $destination,
    int $fuel
) {
    expect(LocationHelper::getRoutePath($origin, $destination, $fuel))
        ->toBeNull();
})->with([
    fn () => [
        ...Waypoint::factory(2)
            ->inSystem()
            ->create(['x' => 1, 'y' => 1]),
        0,
    ],
    fn () => [
        ...Waypoint::factory(2)
            ->state(new Sequence(
                ['x' => 1, 'y' => 1],
                ['x' => 2, 'y' => 2],
            ))
            ->inSystem()
            ->create(),
        0,
    ],
    fn () => [
        ...Waypoint::factory(2)
            ->state(new Sequence(
                ['x' => 1, 'y' => 1],
                ['x' => 1000, 'y' => 1000],
            ))
            ->inSystem()
            ->create(),
        0,
    ],
    fn () => [
        ...Waypoint::factory(2)
            ->inSystem()
            ->create(['x' => 1, 'y' => 1]),
        100,
    ],
    fn () => [
        ...Waypoint::factory(2)
            ->state(new Sequence(
                ['x' => 1, 'y' => 1],
                ['x' => 2, 'y' => 2],
            ))
            ->inSystem()
            ->create(),
        100,
    ],
    fn () => [
        ...Waypoint::factory(2)
            ->state(new Sequence(
                ['x' => 1, 'y' => 1],
                ['x' => 1000, 'y' => 1000],
            ))
            ->inSystem()
            ->create(),
        100,
    ],
    function () {
        $waypoints = Waypoint::factory(3)
            ->inSystem()
            ->create(['x' => 1, 'y' => 1]);

        return [$waypoints->get(0), $waypoints->get(1), 100];
    },
    function () {
        $waypoints = Waypoint::factory(3)
            ->inSystem()
            ->state(new Sequence(
                ['x' => 1, 'y' => 1],
                ['x' => 1000, 'y' => 1000],
                ['x' => 1, 'y' => 1],
            ))
            ->create();

        return [$waypoints->get(0), $waypoints->get(1), 100];
    },
    function () {
        $systems = System::factory(2)->create();
        // systems NOT connected
        $waypoints = Waypoint::factory(2)
            ->inSystem($systems->get(0))
            ->canRefuel()
            ->state(new Sequence(
                ['x' => 1, 'y' => 1, 'type' => WaypointTypes::PLANET],
                ['x' => 50, 'y' => 50, 'type' => WaypointTypes::JUMP_GATE],
            ))
            ->create()
            ->concat(
                Waypoint::factory(2)
                    ->inSystem($systems->get(1))
                    ->canRefuel()
                    ->state(new Sequence(
                        ['x' => 50, 'y' => 50, 'type' => WaypointTypes::JUMP_GATE],
                        ['x' => 1, 'y' => 1, 'type' => WaypointTypes::PLANET],
                    ))
                    ->create()
            );

        return [
            $waypoints->get(0),
            $waypoints->get(3),
            100,
            [
                $waypoints->get(0)->symbol,
                $waypoints->get(1)->symbol,
                $waypoints->get(2)->symbol,
                $waypoints->get(3)->symbol,
            ],
        ];
    },
]);

test('getRoutePath returns the path', function (
    Waypoint $origin,
    Waypoint $destination,
    int $fuel,
    ?array $expected
) {
    expect(LocationHelper::getRoutePath($origin, $destination, $fuel))
        ->not()->toBeNull()
        ->toBe($expected);
})->with([
    function () {
        $waypoints = Waypoint::factory(3)
            ->inSystem()
            ->canRefuel()
            ->state(new Sequence(
                ['x' => 1, 'y' => 1],
                ['x' => 50, 'y' => 50],
                ['x' => 100, 'y' => 100],
            ))
            ->create();

        return [
            $waypoints->get(0),
            $waypoints->get(2),
            100,
            [
                $waypoints->get(0)->symbol,
                $waypoints->get(1)->symbol,
                $waypoints->get(2)->symbol,
            ],
        ];
    },
    function () {
        $waypoints = Waypoint::factory(4)
            ->inSystem()
            ->canRefuel()
            ->state(new Sequence(
                ['x' => 1, 'y' => 1],
                ['x' => 50, 'y' => 50],
                ['x' => 100, 'y' => 100],
                ['x' => 150, 'y' => 150],
            ))
            ->create();

        return [
            $waypoints->get(0),
            $waypoints->get(3),
            100,
            [
                $waypoints->get(0)->symbol,
                $waypoints->get(1)->symbol,
                $waypoints->get(2)->symbol,
                $waypoints->get(3)->symbol,
            ],
        ];
    },
    function () {
        $waypoints = Waypoint::factory(3)
            ->inSystem()
            ->canRefuel()
            ->state(new Sequence(
                ['x' => -50, 'y' => -50],
                ['x' => 1, 'y' => 1],
                ['x' => 50, 'y' => 50],
            ))
            ->create();

        return [
            $waypoints->get(0),
            $waypoints->get(2),
            100,
            [
                $waypoints->get(0)->symbol,
                $waypoints->get(1)->symbol,
                $waypoints->get(2)->symbol,
            ],
        ];
    },
    function () {
        $systems = System::factory(2)->create();
        $systems->get(0)->connections()->attach($systems->get(1));
        $systems->get(1)->connections()->attach($systems->get(0));
        $waypoints = Waypoint::factory(2)
            ->inSystem($systems->get(0))
            ->canRefuel()
            ->state(new Sequence(
                ['x' => 1, 'y' => 1, 'type' => WaypointTypes::PLANET],
                ['x' => 50, 'y' => 50, 'type' => WaypointTypes::JUMP_GATE],
            ))
            ->create()
            ->concat(
                Waypoint::factory(2)
                    ->inSystem($systems->get(1))
                    ->canRefuel()
                    ->state(new Sequence(
                        ['x' => 50, 'y' => 50, 'type' => WaypointTypes::JUMP_GATE],
                        ['x' => 1, 'y' => 1, 'type' => WaypointTypes::PLANET],
                    ))
                    ->create()
            );

        return [
            $waypoints->get(0),
            $waypoints->get(3),
            100,
            [
                $waypoints->get(0)->symbol,
                $waypoints->get(1)->symbol,
                $waypoints->get(2)->symbol,
                $waypoints->get(3)->symbol,
            ],
        ];
    },
]);
