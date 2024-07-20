<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\TradeGoodTypes;
use App\Enums\TradeSymbols;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;

class MarketGood extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'trade_symbol',
    ];

    public function waypoint(): HasOne
    {
        return $this->hasOne(Waypoint::class, 'symbol', 'waypoint_symbol');
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
            'waypoint_symbol' => 'string',
            'type', TradeGoodTypes::class,
            'trade_symbol', TradeSymbols::class,
        ];
    }
}
