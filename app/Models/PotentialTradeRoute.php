<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ActivityLevels;
use App\Enums\SupplyLevels;
use App\Enums\TradeSymbols;

class PotentialTradeRoute extends Model
{
    protected $fillable = [
        'trade_symbol',
        'origin',
        'destination',
        'purchase_price',
        'supply_at_origin',
        'activity_at_origin',
        'trade_volume_at_origin',
        'sell_price',
        'supply_at_destination',
        'activity_at_destination',
        'trade_volume_at_destination',
        'distance',
    ];

    protected $casts = [
        'trade_symbol' => TradeSymbols::class,
        'origin' => 'string',
        'destination' => 'string',
        'purchase_price' => 'integer',
        'supply_at_origin' => SupplyLevels::class,
        'activity_at_origin' => ActivityLevels::class,
        'trade_volume_at_origin' => 'integer',
        'sell_price' => 'integer',
        'supply_at_destination' => SupplyLevels::class,
        'activity_at_destination' => ActivityLevels::class,
        'trade_volume_at_destination' => 'integer',
        'distance' => 'integer',
    ];

    public function getProfitAttribute(): float|int
    {
        return $this->purchase_price
            ? ($this->sell_price - $this->purchase_price) / $this->purchase_price
            : 0;
    }

    public function getProfitPerFlightAttribute(): float|int
    {
        return $this->sell_price && $this->purchase_price
            ? ($this->sell_price - $this->purchase_price) * $this->trade_volume_at_origin
            : 0;
    }
}
