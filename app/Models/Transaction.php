<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\TradeSymbols;
use App\Enums\TransactionTypes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string $agent_symbol
 * @property string $ship_symbol
 * @property string $waypoint_symbol
 * @property Carbon $timestamp
 * @property TransactionTypes $type
 * @property TradeSymbols|null $trade_symbol
 * @property int $units
 * @property int $price_per_unit
 * @property int $total_price
 * @property-read Agent|null $agent
 * @property-read Ship|null $ship
 * @property-read Waypoint|null $waypoint
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Transaction newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Transaction newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Transaction query()
 * @method static \Illuminate\Database\Eloquent\Builder|Transaction whereAgentSymbol($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transaction whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transaction whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transaction wherePricePerUnit($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transaction whereShipSymbol($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transaction whereTimestamp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transaction whereTotalPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transaction whereTradeSymbol($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transaction whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transaction whereUnits($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transaction whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transaction whereWaypointSymbol($value)
 *
 * @mixin \Eloquent
 */
class Transaction extends Model
{
    protected $fillable = [
        'agent_symbol',
        'ship_symbol',
        'waypoint_symbol',
        'timestamp',
        'type',
        'trade_symbol',
        'units',
        'price_per_unit',
        'total_price',
    ];

    public function ship(): BelongsTo
    {
        return $this->belongsTo(Ship::class, 'ship_symbol', 'symbol');
    }

    public function waypoint(): BelongsTo
    {
        return $this->belongsTo(Waypoint::class, 'waypoint_symbol', 'symbol');
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class, 'agent_symbol', 'symbol');
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
            'timestamp' => 'datetime',
            'type' => TransactionTypes::class,
            'trade_symbol' => TradeSymbols::class,
            'units' => 'integer',
            'price_per_unit' => 'integer',
            'total_price' => 'integer',
        ];
    }
}
