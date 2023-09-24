<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\TradeSymbols;
use Illuminate\Database\Eloquent\Factories\HasFactory;

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
    ];

    public function contract()
    {
        return $this->belongsTo(Contract::class);
    }
}
