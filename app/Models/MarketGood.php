<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\TradeGoodTypes;
use App\Enums\TradeSymbols;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string $waypoint_symbol
 * @property string $type
 * @property string $trade_symbol
 * @property mixed $0
 * @property TradeGoodTypes $1
 * @property mixed $2
 * @property TradeSymbols $3
 * @property-read Waypoint|null $waypoint
 *
 * @method static \Database\Factories\MarketGoodFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder|MarketGood newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MarketGood newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MarketGood query()
 *
 * @mixin \Eloquent
 */
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
