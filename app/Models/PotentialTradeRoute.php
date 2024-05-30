<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ActivityLevels;
use App\Enums\SupplyLevels;
use App\Enums\TradeSymbols;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
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
 * @property string|null $origin_type
 * @property string|null $destination_type
 * @property int $origin_x
 * @property int $origin_y
 * @property int $destination_x
 * @property int $destination_y
 * @property int|null $distance
 * @property float|null $profit
 * @property int|null $profit_per_flight
 * @property int|null $ship_id
 * @property-read Ship|null $ship
 *
 * @method static \Illuminate\Database\Eloquent\Builder|PotentialTradeRoute newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PotentialTradeRoute newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PotentialTradeRoute query()
 *
 * @mixin \Eloquent
 */
class PotentialTradeRoute extends Model
{
    public const CACHE_TAG = 'CURRENTLY_SERVED';

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

    public function ship(): BelongsTo
    {
        return $this->belongsTo(Ship::class);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
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
}
