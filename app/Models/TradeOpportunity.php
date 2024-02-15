<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\SupplyLevels;
use App\Enums\TradeSymbols;
use App\Enums\ActivityLevels;
use App\Enums\TradeGoodTypes;
use App\Helpers\LocationHelper;
use App\Traits\FindableBySymbol;
use Illuminate\Support\Collection;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\TradeOpportunity
 *
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property TradeSymbols $symbol
 * @property string $waypoint_symbol
 * @property int $purchase_price
 * @property int $sell_price
 * @property TradeGoodTypes $type
 * @property int $trade_volume
 * @property SupplyLevels $supply
 * @property ActivityLevels|null $activity
 * @method static \Illuminate\Database\Eloquent\Builder|TradeOpportunity bySymbol(\App\Enums\TradeSymbols|string $symbol)
 * @method static \Illuminate\Database\Eloquent\Builder|TradeOpportunity exchanges()
 * @method static \Illuminate\Database\Eloquent\Builder|TradeOpportunity exports()
 * @method static \Illuminate\Database\Eloquent\Builder|TradeOpportunity forCargos(\App\Models\Ship $ship)
 * @method static \Illuminate\Database\Eloquent\Builder|TradeOpportunity imports()
 * @method static \Illuminate\Database\Eloquent\Builder|TradeOpportunity newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TradeOpportunity newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TradeOpportunity query()
 * @method static \Illuminate\Database\Eloquent\Builder|TradeOpportunity whereActivity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TradeOpportunity whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TradeOpportunity whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TradeOpportunity wherePurchasePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TradeOpportunity whereSellPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TradeOpportunity whereSupply($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TradeOpportunity whereSymbol($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TradeOpportunity whereTradeVolume($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TradeOpportunity whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TradeOpportunity whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TradeOpportunity whereWaypointSymbol($value)
 * @mixin \Eloquent
 */
class TradeOpportunity extends Model
{
    use FindableBySymbol;

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

    protected $casts = [
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
        return $query->where('symbol', TradeGoodTypes::fromName($symbol));
    }

    public function scopeForCargos(Builder $query, Ship $ship): Builder
    {
        return $query->imports()
            ->whereIn('symbol', $ship->cargos()->pluck('symbol'));
    }

    /**
     * @template TWaypointSymbol string
     *
     * @return Collection<TWaypointSymbol, array>
     */
    public static function marketplacesForCargos(Ship $ship): Collection
    {
        return static::forCargos($ship)
            ->get()
            ->map(fn (self $tradeOpportunity) => [
                ...$tradeOpportunity->only([
                    'symbol',
                    'waypoint_symbol',
                    'purchase_price',
                ]),
                'distance' => LocationHelper::distance(
                    $ship->waypoint_symbol,
                    $tradeOpportunity->waypoint_symbol
                ),
            ])
            ->groupBy('symbol')
            ->when(
                $ship->fuel_capacity > 0,
                fn (Collection $tradeOpportunities) => $tradeOpportunities->map(
                    fn (Collection $tradeOpportunities) => $tradeOpportunities
                        ->filter(fn (array $tradeOpportunity) => $tradeOpportunity['distance'] <= $ship->fuel_capacity)
                )
            );

    }

    /**
     * @template TWaypointSymbol string
     *
     * @return Collection<TWaypointSymbol, array>
     */
    public static function bestMarketplacesForCargos(Ship $ship): Collection
    {
        return static::marketplacesForCargos($ship)
            ->map(
                fn (Collection $tradeOpportunities) => $tradeOpportunities
                    ->sortBy(
                        fn (array $tradeOpportunity) => $tradeOpportunity['purchase_price'] / $tradeOpportunity['distance'],
                        descending: true
                    )
                    ->first()
            );
    }

    /**
     * @template TWaypointSymbol string
     *
     * @return Collection<TWaypointSymbol, array>
     */
    public static function randomMarketplacesForCargos(Ship $ship): Collection
    {
        return static::marketplacesForCargos($ship)
            ->map(fn (Collection $tradeOpportunities) => $tradeOpportunities->random());
    }
}
