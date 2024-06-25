<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\WaypointTraitSymbols;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\WaypointTrait>
 */
class WaypointTraitFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'symbol' => $this->faker->unique->randomElement(WaypointTraitSymbols::values()),
            'name' => $this->faker->word,
            'description' => $this->faker->sentence,
        ];
    }

    public function marketplace(): static
    {
        return $this->state(fn (array $attributes) => [
            'symbol' => WaypointTraitSymbols::MARKETPLACE,
        ]);
    }
}
