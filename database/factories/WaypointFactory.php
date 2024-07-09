<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\TradeGoodTypes;
use App\Enums\TradeSymbols;
use App\Enums\WaypointTraitSymbols;
use App\Enums\WaypointTypes;
use App\Models\Faction;
use App\Models\System;
use App\Models\TradeOpportunity;
use App\Models\Waypoint;
use App\Models\WaypointTrait;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Waypoint>
 */
class WaypointFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $systemSymbol = $this->faker->systemSymbol();
        $factionId = (Faction::first() ?? Faction::factory()->create())->id;

        return [
            'symbol' => $systemSymbol . '-' . $this->faker->waypointSuffix(),
            'system_symbol' => $systemSymbol,
            'type' => WaypointTypes::randomCase(),
            'x' => $this->faker->numberBetween(-1000, 1000),
            'y' => $this->faker->numberBetween(-1000, 1000),
            'faction_id' => $factionId,
            'orbits' => null,
            'is_under_construction' => false,
        ];
    }

    /**
     * Sets System for Waypoints.
     */
    public function inSystem(string|System $system = 'X1-AB12'): Factory
    {
        $system = $system instanceof System
            ? $system
            : (System::findBySymbol($system)
                ?? System::factory()->createOne(['symbol' => $system]));

        return $this->for($system)
            ->set('system_symbol', $system->symbol)
            ->state(fn (array $attributes) => [
                'symbol' => $attributes['system_symbol']
                    . '-'
                    . $this->faker->unique()->waypointSuffix(),
            ]);
    }

    public function isMarketplace(): Factory
    {
        $trait = WaypointTrait::firstWhere('symbol', WaypointTraitSymbols::MARKETPLACE)
            ?? WaypointTrait::factory()->marketplace()->createOne();

        return $this->hasAttached($trait, relationship: 'traits');
    }

    public function canRefuel(): Factory
    {
        return $this->isMarketplace()
            ->afterCreating(
                fn (Waypoint $waypoint) => $waypoint
                    ->tradeOpportunities()
                    ->save(
                        TradeOpportunity::factory()->createOne([
                            'waypoint_symbol' => $waypoint->symbol,
                            'symbol' => TradeSymbols::FUEL,
                            'type' => TradeGoodTypes::EXPORT,
                        ])
                    )
            );
    }
}
