<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\TradeSymbols;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property int $contract_id
 * @property TradeSymbols $trade_symbol
 * @property string $destination_symbol
 * @property int $units_required
 * @property int $units_fulfilled
 * @property-read Contract $contract
 * @property-read bool $is_done
 * @property-read int $units_to_be_delivered
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Delivery newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Delivery newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Delivery onlyUnfulfilled()
 * @method static \Illuminate\Database\Eloquent\Builder|Delivery query()
 *
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

    public function scopeOnlyUnfulfilled(Builder $query): Builder
    {
        return $query->whereColumn('units_required', '>', 'units_fulfilled');
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
            'destination_symbol' => 'string',
            'units_required' => 'integer',
            'units_fulfilled' => 'integer',
            'units_to_be_delivered' => 'integer',
        ];
    }
}
