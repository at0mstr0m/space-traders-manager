<?php

declare(strict_types=1);

namespace App\Models;

use App\Actions\UpdateWaypointAction;
use App\Enums\ShipNavStatus;
use App\Enums\TradeGoodTypes;
use App\Enums\TradeSymbols;
use App\Enums\WaypointTraitSymbols;
use App\Enums\WaypointTypes;
use App\Helpers\LocationHelper;
use App\Helpers\SpaceTraders;
use App\Traits\FindableBySymbol;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

/**
 * @property int $id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string $symbol
 * @property string $system_symbol
 * @property WaypointTypes $type
 * @property int|null $faction_id
 * @property int $x
 * @property int $y
 * @property string|null $orbits
 * @property bool|null $is_under_construction
 * @property-read Faction|null $faction
 * @property-read bool $can_refuel
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\WaypointModifier> $modifiers
 * @property-read int|null $modifiers_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Waypoint> $orbitals
 * @property-read int|null $orbitals_count
 * @property-read Waypoint|null $orbitedWaypoint
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Ship> $ships
 * @property-read int|null $ships_count
 * @property-read System|null $system
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\TradeOpportunity> $tradeOpportunities
 * @property-read int|null $trade_opportunities_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\WaypointTrait> $traits
 * @property-read int|null $traits_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Waypoint onlyCanRefuel()
 * @method static \Illuminate\Database\Eloquent\Builder|Waypoint newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Waypoint newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Waypoint query()
 * @method static \Illuminate\Database\Eloquent\Builder|Waypoint onlyCanBeMined()
 * @method static \Illuminate\Database\Eloquent\Builder|Waypoint onlyCanBeSiphoned()
 * @method static \Illuminate\Database\Eloquent\Builder|Waypoint searchBySymbol(string $search = '')
 *
 * @mixin \Eloquent
 */
class Waypoint extends Model
{
    use FindableBySymbol;
    use HasFactory;

    protected $fillable = [
        'symbol',
        'system_symbol',
        'type',
        'x',
        'y',
        'faction_id',
        'orbits',
        'is_under_construction',
    ];

    /**
     * @return bool
     */
    public function getCanRefuelAttribute()
    {
        return Cache::remember(
            'waypoint_can_refuel:' . $this->symbol,
            now()->addMinutes(15),
            fn () => $this->traits()
                ->where('symbol', WaypointTraitSymbols::MARKETPLACE)
                ->exists()
                && $this->marketGoods()
                    ->where('trade_symbol', TradeSymbols::FUEL)
                    ->whereNot('type', TradeGoodTypes::IMPORT)
                    ->exists()
        );
    }

    public function faction(): BelongsTo
    {
        return $this->belongsTo(Faction::class);
    }

    public function traits(): BelongsToMany
    {
        return $this->belongsToMany(WaypointTrait::class);
    }

    public function modifiers(): BelongsToMany
    {
        return $this->belongsToMany(WaypointModifier::class);
    }

    public function orbitedWaypoint(): BelongsTo
    {
        return $this->belongsTo(static::class, 'orbits', 'symbol');
    }

    public function orbitals(): HasMany
    {
        return $this->hasMany(static::class, 'orbits', 'symbol');
    }

    public function tradeOpportunities(): HasMany
    {
        return $this->hasMany(TradeOpportunity::class, 'waypoint_symbol', 'symbol');
    }

    public function ships(): HasMany
    {
        return $this->hasMany(Ship::class, 'waypoint_symbol', 'symbol');
    }

    public function system(): BelongsTo
    {
        return $this->belongsTo(System::class, 'system_symbol', 'symbol');
    }

    public function marketGoods(): HasMany
    {
        return $this->hasMany(MarketGood::class, 'waypoint_symbol', 'symbol');
    }

    public function scopeOnlyCanRefuel(Builder $query): Builder
    {
        return $query->whereRelation('traits', 'symbol', WaypointTraitSymbols::MARKETPLACE)
            ->whereRelation(
                'marketGoods',
                fn (Builder $query) => $query->where([
                    ['trade_symbol', '=', TradeSymbols::FUEL],
                    ['type', '<>', TradeGoodTypes::IMPORT],
                ])
            );
    }

    public function scopeOnlyCanBeMined(Builder $query): Builder
    {
        return $query->whereIn(
            'type',
            [WaypointTypes::ASTEROID, WaypointTypes::ENGINEERED_ASTEROID]
        );
    }

    public function scopeOnlyCanBeSiphoned(Builder $query): Builder
    {
        return $query->where('type', WaypointTypes::GAS_GIANT);
    }

    public function scopeOnlyHavingShipPresent(Builder $query): Builder
    {
        return $query->whereRelation('ships', 'status', '<>', ShipNavStatus::IN_TRANSIT);
    }

    public function closestRefuelingWaypoint(bool $excludeSelf = true): ?static
    {
        $waypoints = Waypoint::where('system_symbol', $this->system_symbol)
            ->onlyCanRefuel()
            ->when(
                $excludeSelf,
                fn (Builder $query) => $query->where([
                    ['symbol', '<>', $this->symbol],
                    ['x', '<>', $this->x],
                    ['y', '<>', $this->y],
                ])
            )
            ->get();

        $waypointSymbol = data_get(
            $waypoints->map(
                fn (Waypoint $waypoint) => [
                    'waypoint_symbol' => $waypoint->symbol,
                    'distance' => LocationHelper::distance($this, $waypoint),
                ]
            )
                ->sortBy('distance')
                ->first(),
            'waypoint_symbol'
        );

        return $waypointSymbol
            ? $waypoints->firstWhere('symbol', $waypointSymbol)
            : null;
    }

    public function refetch(): static
    {
        /** @var SpaceTraders */
        $api = app(SpaceTraders::class);

        return UpdateWaypointAction::run($api->getWaypoint($this->symbol));
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
            'symbol' => 'string',
            'system_symbol' => 'string',
            'type' => WaypointTypes::class,
            'x' => 'integer',
            'y' => 'integer',
            'is_under_construction' => 'boolean',
        ];
    }
}
