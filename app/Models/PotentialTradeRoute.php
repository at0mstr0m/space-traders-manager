<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\Supplies;
use App\Enums\TradeSymbols;

class PotentialTradeRoute extends Model
{
    protected $fillable = [
        'trade_symbol',
        'origin',
        'destination',
        'purchase_price',
        'supply_at_origin',
        'trade_volume_at_origin',
        'sell_price',
        'supply_at_destination',
        'trade_volume_at_destination',
    ];

    protected $casts = [
        'trade_symbol' => TradeSymbols::class,
        'origin' => 'string',
        'destination' => 'string',
        'purchase_price' => 'integer',
        'supply_at_origin' => Supplies::class,
        'trade_volume_at_origin' => 'integer',
        'sell_price' => 'integer',
        'supply_at_destination' => Supplies::class,
        'trade_volume_at_destination' => 'integer',
    ];

    public function getProfitAttribute(): float|int
    {
        return $this->sell_price / $this->purchase_price;
    }
}
