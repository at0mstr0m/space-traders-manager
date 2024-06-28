<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\WaypointTypes;
use App\Models\Faction;
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
    public function inSystem(?string $systemSymbol = null): Factory
    {
        return $this->set(
            'system_symbol',
            $systemSymbol ??= $this->faker->systemSymbol()
        )->state(
            fn (array $attributes) => [
                'symbol' => $attributes['system_symbol']
                    . '-'
                    . $this->faker->unique()->waypointSuffix(),
            ]
        );
    }
}
