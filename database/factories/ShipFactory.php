<?php

namespace Database\Factories;

use App\Enums\CrewRotations;
use App\Enums\FlightModes;
use App\Enums\ShipNavStatus;
use App\Enums\ShipRoles;
use App\Models\System;
use App\Models\Waypoint;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Ship>
 */
class ShipFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'symbol' => $this->faker->shipSymbol(),
            'role' => ShipRoles::randomCase(),
            'waypoint_symbol' => $this->faker->waypointSymbol(),
            'status' => ShipNavStatus::IN_ORBIT,
            'flight_mode' => FlightModes::CRUISE,
            'crew_current' => 0,
            'crew_capacity' => 0,
            'crew_required' => 0,
            'crew_rotation' => CrewRotations::STRICT,
            'crew_morale' => 100,
            'crew_wages' => 0,
            'fuel_current' => 0,
            'fuel_capacity' => 0,
            'fuel_consumed' => 0,
            'cooldown' => 0,
            'frame_condition' => 1.0,
            'frame_integrity' => 1.0,
            'reactor_condition' => 1.0,
            'reactor_integrity' => 1.0,
            'engine_condition' => 1.0,
            'engine_integrity' => 1.0,
            'cargo_capacity' => 0,
            'cargo_units' => 0,
        ];
    }

    public function inSystem(string|System|Waypoint $system = 'X1-AB12'): Factory
    {
        if ($system instanceof Waypoint) {
            $system = $system->system_symbol;
        } elseif (is_string($system)) {
            $system = System::findBySymbol($system)
                ?? System::factory()->createOne(['symbol' => $system]);
        }

        return $this->state(fn (array $attributes) => [
            'waypoint_symbol' => $system->symbol
                . '-'
                . $this->faker->unique()->waypointSuffix(),
        ]);
    }

    public function atWaypoint(string|Waypoint $waypoint): Factory
    {
        if ($waypoint instanceof Waypoint) {
            $waypoint = $waypoint->symbol;
        }

        return $this->state(fn (array $attributes) => [
            'waypoint_symbol' => $waypoint,
        ]);
    }

    public function fullyFueled(int $fuelCapacity = 100): Factory
    {
        return $this->state(fn (array $attributes) => [
            'fuel_current' => $fuelCapacity,
            'fuel_capacity' => $fuelCapacity,
        ]);
    }
}
