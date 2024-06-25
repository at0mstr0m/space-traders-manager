<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\ActivityLevels;
use App\Enums\SupplyLevels;
use App\Enums\TradeGoodTypes;
use App\Enums\TradeSymbols;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TradeOpportunity>
 */
class TradeOpportunityFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'waypoint_symbol' => $this->faker->waypointSymbol(),
            'symbol' => $this->faker->randomElement(TradeSymbols::values()),
            'purchase_price' => $this->faker->randomNumber(),
            'sell_price' => $this->faker->randomNumber(),
            'type' => $this->faker->randomElement(TradeGoodTypes::values()),
            'trade_volume' => $this->faker->randomNumber(),
            'supply' => $this->faker->randomElement(SupplyLevels::values()),
            'activity' => $this->faker->randomElement(ActivityLevels::values()),
        ];
    }
}
