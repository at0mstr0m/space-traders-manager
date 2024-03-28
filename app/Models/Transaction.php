<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\TradeSymbols;
use App\Enums\TransactionTypes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
