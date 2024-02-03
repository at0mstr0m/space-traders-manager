<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ActivityLevels;
use App\Enums\SupplyLevels;
use App\Enums\TradeGoodTypes;
use App\Enums\TradeSymbols;

class TradeOpportunity extends Model
{
    protected $fillable = [
        'waypoint_symbol',
        'symbol',
        'purchase_price',
        'sell_price',
        'type',
        'trade_volume',
        'supply',
        'activity',
    ];

    protected $casts = [
        'waypoint_symbol' => 'string',
        'symbol' => TradeSymbols::class,
        'purchase_price' => 'integer',
        'sell_price' => 'integer',
        'type' => TradeGoodTypes::class,
        'trade_volume' => 'integer',
        'supply' => SupplyLevels::class,
        'activity' => ActivityLevels::class,
    ];
}
