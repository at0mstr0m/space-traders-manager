<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ActivityLevels;
use App\Enums\SupplyLevels;
use App\Enums\TradeSymbols;

/**
 * App\Models\PotentialTradeRoute
 *
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property TradeSymbols $trade_symbol
 * @property string $origin
 * @property string $destination
 * @property int|null $purchase_price
 * @property SupplyLevels|null $supply_at_origin
 * @property ActivityLevels|null $activity_at_origin
 * @property int|null $trade_volume_at_origin
 * @property int|null $sell_price
 * @property SupplyLevels|null $supply_at_destination
 * @property ActivityLevels|null $activity_at_destination
 * @property int|null $trade_volume_at_destination
 * @property int $origin_x
 * @property int $origin_y
 * @property int $destination_x
 * @property int $destination_y
 * @property int|null $distance
 * @property float|null $profit
 * @property int|null $profit_per_flight
 * @method static \Illuminate\Database\Eloquent\Builder|PotentialTradeRoute newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PotentialTradeRoute newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PotentialTradeRoute query()
 * @method static \Illuminate\Database\Eloquent\Builder|PotentialTradeRoute whereActivityAtDestination($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PotentialTradeRoute whereActivityAtOrigin($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PotentialTradeRoute whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PotentialTradeRoute whereDestination($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PotentialTradeRoute whereDestinationX($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PotentialTradeRoute whereDestinationY($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PotentialTradeRoute whereDistance($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PotentialTradeRoute whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PotentialTradeRoute whereOrigin($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PotentialTradeRoute whereOriginX($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PotentialTradeRoute whereOriginY($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PotentialTradeRoute whereProfit($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PotentialTradeRoute whereProfitPerFlight($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PotentialTradeRoute wherePurchasePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PotentialTradeRoute whereSellPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PotentialTradeRoute whereSupplyAtDestination($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PotentialTradeRoute whereSupplyAtOrigin($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PotentialTradeRoute whereTradeSymbol($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PotentialTradeRoute whereTradeVolumeAtDestination($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PotentialTradeRoute whereTradeVolumeAtOrigin($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PotentialTradeRoute whereUpdatedAt($value)
 * @mixin \Eloquent
 */
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
        'origin_x',
        'origin_y',
        'destination_x',
        'destination_y',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
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
        'origin_x' => 'integer',
        'origin_y' => 'integer',
        'destination_x' => 'integer',
        'destination_y' => 'integer',
        'distance' => 'integer',
        'profit' => 'float',
        'profit_per_flight' => 'integer',
    ];
}
