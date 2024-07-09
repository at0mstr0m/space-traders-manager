<?php

namespace Database\Factories;

use App\Enums\SystemTypes;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\System>
 */
class SystemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'symbol' => $this->faker->systemSymbol(),
            'sector_symbol' => $this->faker->sectorSymbol(),
            'type' => $this->faker->randomElement(SystemTypes::values()),
            'x' => $this->faker->numberBetween(-10_000, 10_000),
            'y' => $this->faker->numberBetween(-10_000, 10_000),
        ];
    }
}
