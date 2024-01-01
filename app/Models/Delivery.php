<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\TradeSymbols;

class Delivery extends Model
{
    protected $fillable = [
        'trade_symbol',
        'destination_symbol',
        'units_required',
        'units_fulfilled',
    ];

    protected $casts = [
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
