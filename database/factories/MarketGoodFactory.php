<?php

namespace Database\Factories;

use App\Enums\TradeGoodTypes;
use App\Enums\TradeSymbols;
use App\Models\Waypoint;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\MarketGood>
 */
class MarketGoodFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'type' => TradeGoodTypes::randomCase(),
            'trade_symbol' => TradeSymbols::randomCase(),
            'waypoint_symbol' => $this->faker->waypointSymbol(),
        ];
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
}
