<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\FactionSymbols;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Faction>
 */
class FactionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $factionSymbol = FactionSymbols::randomCase();

        return [
            'symbol' => $factionSymbol,
            'name' => Str::of($factionSymbol->value)
                ->replaceMatches('/_/', ' ')
                ->title()
                ->toString(),
            'headquarters' => $this->faker->waypointSymbol(),
            'description' => $this->faker->sentence(),
            'is_recruiting' => $this->faker->boolean(),
        ];
    }
}
