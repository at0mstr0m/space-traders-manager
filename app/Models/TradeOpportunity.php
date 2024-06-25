<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ActivityLevels;
use App\Enums\SupplyLevels;
use App\Enums\TradeGoodTypes;
use App\Enums\TradeSymbols;
use App\Helpers\LocationHelper;
use App\Traits\FindableBySymbol;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * @property int $id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property TradeSymbols $symbol
 * @property string $waypoint_symbol
 * @property int $purchase_price
 * @property int $sell_price
 * @property TradeGoodTypes $type
 * @property int $trade_volume
 * @property SupplyLevels $supply
 * @property ActivityLevels|null $activity
 * @property-read Waypoint|null $waypoint
 *
 * @method static \Illuminate\Database\Eloquent\Builder|TradeOpportunity bySymbol(\App\Enums\TradeSymbols|string $symbol)
 * @method static \Illuminate\Database\Eloquent\Builder|TradeOpportunity exchanges()
 * @method static \Illuminate\Database\Eloquent\Builder|TradeOpportunity exports()
 * @method static \Illuminate\Database\Eloquent\Builder|TradeOpportunity forCargos(\App\Models\Ship $ship)
 * @method static \Illuminate\Database\Eloquent\Builder|TradeOpportunity imports()
 * @method static \Illuminate\Database\Eloquent\Builder|TradeOpportunity newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TradeOpportunity newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TradeOpportunity query()
 * @method static \Illuminate\Database\Eloquent\Builder|TradeOpportunity searchBySymbol(string $search = '')
 *
 * @mixin \Eloquent
 */
class TradeOpportunity extends Model
{
    use FindableBySymbol;
    use HasFactory;

    protected $fillable = [
        'waypoint_symbol',
        'symbol',
        'purchase_price',
        'sell_price',
        'type',
        'trade_volume',
        'supply',
        'activity',
    ];

    public function waypoint(): BelongsTo
    {
        return $this->belongsTo(Waypoint::class, 'symbol', 'waypoint_symbol');
    }

    public function scopeExports(Builder $query): Builder
    {
        return $query->where('type', TradeGoodTypes::EXPORT);
    }

    public function scopeImports(Builder $query): Builder
    {
        return $query->where('type', TradeGoodTypes::IMPORT);
    }

    public function scopeExchanges(Builder $query): Builder
    {
        return $query->where('type', TradeGoodTypes::EXCHANGE);
    }

    public function scopeBySymbol(Builder $query, string|TradeSymbols $symbol): Builder
    {
        return $query->where('symbol', TradeSymbols::fromName($symbol));
    }

    public function scopeForCargos(Builder $query, Ship $ship): Builder
    {
        return $query->imports()
            ->whereNotIn('supply', [SupplyLevels::ABUNDANT, SupplyLevels::HIGH])
            ->whereIn('symbol', $ship->cargos()->pluck('symbol'));
    }

    /**
     * @template TWaypointSymbol string
     *
     * @return Collection<TWaypointSymbol, array>
     */
    public static function marketplacesForCargos(
        Ship $ship,
        bool $onlyDirectlyReachable = true
    ): Collection {
        return static::forCargos($ship)
            ->get()
            ->map(fn (self $tradeOpportunity) => [
                ...$tradeOpportunity->only([
                    'symbol',
                    'waypoint_symbol',
                    'sell_price',
                    'trade_volume',
                ]),
                'distance' => LocationHelper::distance(
                    $ship->waypoint_symbol,
                    $tradeOpportunity->waypoint_symbol
                ),
            ])
            ->groupBy('symbol')
            ->when(
                $onlyDirectlyReachable && $ship->fuel_capacity > 0,
                fn (Collection $tradeOpportunities) => $tradeOpportunities->map(
                    fn (Collection $tradeOpportunities) => $tradeOpportunities
                        ->filter(fn (array $tradeOpportunity) => $tradeOpportunity['distance'] <= $ship->fuel_capacity)
                )
            )
            ->reject(fn (Collection $tradeOpportunities) => $tradeOpportunities->isEmpty());
    }

    /**
     * @template TWaypointSymbol string
     *
     * @return Collection<TWaypointSymbol, array>
     */
    public static function mostEfficientMarketplacesForCargos(
        Ship $ship,
        bool $onlyDirectlyReachable = true
    ): Collection {
        return static::marketplacesForCargos($ship, $onlyDirectlyReachable)
            ->map(
                fn (Collection $tradeOpportunities) => $tradeOpportunities
                    ->sortByDesc(
                        fn (array $tradeOpportunity) => $tradeOpportunity['sell_price'] / $tradeOpportunity['distance']
                    )
                    ->first()
            );
    }

    /**
     * @template TWaypointSymbol string
     *
     * @return Collection<TWaypointSymbol, array>
     */
    public static function randomMarketplacesForCargos(
        Ship $ship,
        bool $onlyDirectlyReachable = true
    ): Collection {
        return static::marketplacesForCargos($ship, $onlyDirectlyReachable)
            ->map(
                fn (Collection $tradeOpportunities) => $tradeOpportunities->random()
            );
    }

    /**
     * @template TWaypointSymbol string
     *
     * @return Collection<TWaypointSymbol, array>
     */
    public static function bestPriceMarketplacesForCargos(
        Ship $ship,
        bool $onlyDirectlyReachable = true
    ): Collection {
        return static::marketplacesForCargos($ship, $onlyDirectlyReachable)
            ->map(
                fn (Collection $tradeOpportunities) => $tradeOpportunities
                    ->sortByDesc('sell_price')
                    ->first()
            );
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
            'symbol' => TradeSymbols::class,
            'purchase_price' => 'integer',
            'sell_price' => 'integer',
            'type' => TradeGoodTypes::class,
            'trade_volume' => 'integer',
            'supply' => SupplyLevels::class,
            'activity' => ActivityLevels::class,
        ];
    }
}
