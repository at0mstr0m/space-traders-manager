<?php

declare(strict_types=1);

namespace Tests\Unit\App\Helpers;

use App\Enums\TradeGoodTypes;
use App\Enums\TradeSymbols;
use App\Enums\WaypointTraitSymbols;
use App\Helpers\LocationHelper;
use App\Models\TradeOpportunity;
use App\Models\Waypoint;
use App\Models\WaypointTrait;
use Illuminate\Database\Eloquent\Factories\Sequence;

test(
    'distance is 1 if waypoints have same coordinates',
    function (int|string|Waypoint $waypoint1, int|string|Waypoint $waypoint2) {
        expect(LocationHelper::distance($waypoint1, $waypoint2))
            ->toBe(1);
    }
)->with([
    fn () => [
        Waypoint::factory()->create(['x' => 1, 'y' => 1]),
        Waypoint::factory()->create(['x' => 1, 'y' => 1]),
    ],
    function () {
        $waypoint = Waypoint::factory()->create(['x' => 1, 'y' => 1]);

        return [
            $waypoint,
            $waypoint,
        ];
    },
    fn () => [
        Waypoint::factory()->create(['x' => 1, 'y' => 1])->symbol,
        Waypoint::factory()->create(['x' => 1, 'y' => 1])->symbol,
    ],
]);

test(
    'getRoutePath returns null',
    function (Waypoint $origin, Waypoint $destination, int $fuel) {
        expect(LocationHelper::getRoutePath($origin, $destination, $fuel))
            ->toBeNull();
    }
)->with([
    fn () => [
        Waypoint::factory()->create(['x' => 1, 'y' => 1]),
        Waypoint::factory()->create(['x' => 1, 'y' => 1]),
        0,
    ],
    fn () => [
        Waypoint::factory()->create(['x' => 1, 'y' => 1]),
        Waypoint::factory()->create(['x' => 2, 'y' => 2]),
        0,
    ],
    fn () => [
        Waypoint::factory()->create(['x' => 1, 'y' => 1]),
        Waypoint::factory()->create(['x' => 1000, 'y' => 1000]),
        0,
    ],
    fn () => [
        Waypoint::factory()->create(['x' => 1, 'y' => 1]),
        Waypoint::factory()->create(['x' => 1, 'y' => 1]),
        100,
    ],
    fn () => [
        Waypoint::factory()->create(['x' => 1, 'y' => 1]),
        Waypoint::factory()->create(['x' => 2, 'y' => 2]),
        100,
    ],
    fn () => [
        Waypoint::factory()->create(['x' => 1, 'y' => 1]),
        Waypoint::factory()->create(['x' => 1000, 'y' => 1000]),
        100,
    ],
    function () {
        $waypoints = Waypoint::factory(3)
            ->state(new Sequence(
                ['x' => 1, 'y' => 1],
                ['x' => 1, 'y' => 1],
                ['x' => 1, 'y' => 1],
            ))
            ->create();

        return [$waypoints->get(0), $waypoints->get(1), 100];
    },
    function () {
        $waypoints = Waypoint::factory(3)
            ->state(new Sequence(
                ['x' => 1, 'y' => 1],
                ['x' => 1000, 'y' => 1000],
                ['x' => 1, 'y' => 1],
            ))
            ->create();

        return [$waypoints->get(0), $waypoints->get(1), 100];
    },
]);

test(
    'getRoutePath returns the path',
    function (Waypoint $origin, Waypoint $destination, int $fuel, ?array $expected) {
        expect(LocationHelper::getRoutePath($origin, $destination, $fuel))
            ->not()->toBeNull()
            ->toBe($expected);
    }
)->with([
    function () {
        $trait = WaypointTrait::firstWhere('symbol', WaypointTraitSymbols::MARKETPLACE)
            ?? WaypointTrait::factory()->marketplace()->createOne();
        $waypoints = Waypoint::factory(3)
            ->inSystem()
            ->hasAttached($trait, relationship: 'traits')
            ->state(new Sequence(
                ['x' => 1, 'y' => 1],
                ['x' => 50, 'y' => 50],
                ['x' => 100, 'y' => 100],
            ))
            ->create()
            ->each(
                fn (Waypoint $waypoint) => $waypoint->tradeOpportunities()
                    ->save(
                        TradeOpportunity::factory([
                            'waypoint_symbol' => $waypoint->symbol,
                            'symbol' => TradeSymbols::FUEL,
                            'type' => TradeGoodTypes::EXPORT,
                        ])->createOne()
                    )
            );

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
        $trait = WaypointTrait::firstWhere('symbol', WaypointTraitSymbols::MARKETPLACE)
            ?? WaypointTrait::factory()->marketplace()->createOne();
        $waypoints = Waypoint::factory(3)
            ->inSystem()
            ->hasAttached($trait, relationship: 'traits')
            ->state(new Sequence(
                ['x' => 1, 'y' => 1],
                ['x' => 50, 'y' => 50],
                ['x' => 100, 'y' => 100],
            ))
            ->create()
            ->each(
                fn (Waypoint $waypoint) => $waypoint->tradeOpportunities()
                    ->save(
                        TradeOpportunity::factory([
                            'waypoint_symbol' => $waypoint->symbol,
                            'symbol' => TradeSymbols::FUEL,
                            'type' => TradeGoodTypes::EXPORT,
                        ])->createOne()
                    )
            );

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
        $trait = WaypointTrait::firstWhere('symbol', WaypointTraitSymbols::MARKETPLACE)
            ?? WaypointTrait::factory()->marketplace()->createOne();
        $waypoints = Waypoint::factory(3)
            ->inSystem()
            ->hasAttached($trait, relationship: 'traits')
            ->state(new Sequence(
                ['x' => 1, 'y' => 1],
                ['x' => 100, 'y' => 100],
                ['x' => 50, 'y' => 50],
            ))
            ->create()
            ->each(
                fn (Waypoint $waypoint) => $waypoint->tradeOpportunities()
                    ->save(
                        TradeOpportunity::factory([
                            'waypoint_symbol' => $waypoint->symbol,
                            'symbol' => TradeSymbols::FUEL,
                            'type' => TradeGoodTypes::EXPORT,
                        ])->createOne()
                    )
            );

        return [
            $waypoints->get(0),
            $waypoints->get(1),
            100,
            [
                $waypoints->get(0)->symbol,
                $waypoints->get(2)->symbol,
                $waypoints->get(1)->symbol,
            ],
        ];
    },
]);
