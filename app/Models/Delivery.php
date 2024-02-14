<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\TradeSymbols;

/**
 * App\Models\Delivery
 *
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $contract_id
 * @property TradeSymbols $trade_symbol
 * @property string $destination_symbol
 * @property int $units_required
 * @property int $units_fulfilled
 * @property-read \App\Models\Contract $contract
 * @property-read bool $is_done
 * @property-read int $units_to_be_delivered
 * @method static \Illuminate\Database\Eloquent\Builder|Delivery newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Delivery newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Delivery query()
 * @method static \Illuminate\Database\Eloquent\Builder|Delivery whereContractId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Delivery whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Delivery whereDestinationSymbol($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Delivery whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Delivery whereTradeSymbol($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Delivery whereUnitsFulfilled($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Delivery whereUnitsRequired($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Delivery whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Delivery extends Model
{
    protected $fillable = [
        'trade_symbol',
        'destination_symbol',
        'units_required',
        'units_fulfilled',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'trade_symbol' => TradeSymbols::class,
        'destination_symbol' => 'string',
        'units_required' => 'integer',
        'units_fulfilled' => 'integer',
        'units_to_be_delivered' => 'integer',
    ];

    public function contract()
    {
        return $this->belongsTo(Contract::class);
    }

    public function getUnitsToBeDeliveredAttribute(): int
    {
        return $this->units_required - $this->units_fulfilled;
    }

    public function getIsDoneAttribute(): bool
    {
        return $this->units_required === $this->units_fulfilled;
    }
}
