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
use Illuminate\Support\Collection;

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
    public static function bestMarketplacesForCargos(Ship $ship): Collection
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
            ->map(
                fn (Collection $tradeOpportunities) => $tradeOpportunities
                    ->sortBy(
                        fn (array $tradeOpportunity) => $tradeOpportunity['purchase_price'] / $tradeOpportunity['distance'],
                        descending: true
                    )
                    ->first()
            );
    }
}
