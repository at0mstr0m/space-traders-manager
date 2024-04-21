<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\TradeGoodTypes;
use App\Enums\TradeSymbols;
use App\Enums\WaypointTraitSymbols;
use App\Enums\WaypointTypes;
use App\Helpers\LocationHelper;
use App\Traits\FindableBySymbol;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * App\Models\Waypoint.
 *
 * @property int $id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string $symbol
 * @property WaypointTypes $type
 * @property int $faction_id
 * @property int $x
 * @property int $y
 * @property string|null $orbits
 * @property bool|null $is_under_construction
 * @property-read Faction $faction
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\WaypointModifier> $modifiers
 * @property-read int|null $modifiers_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Waypoint> $orbitals
 * @property-read int|null $orbitals_count
 * @property-read Waypoint|null $orbitedWaypoint
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\TradeOpportunity> $tradeOpportunities
 * @property-read int|null $trade_opportunities_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\WaypointTrait> $traits
 * @property-read int|null $traits_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Waypoint bySystem(string $systemSymbol)
 * @method static \Illuminate\Database\Eloquent\Builder|Waypoint canRefuel()
 * @method static \Illuminate\Database\Eloquent\Builder|Waypoint newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Waypoint newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Waypoint query()
 * @method static \Illuminate\Database\Eloquent\Builder|Waypoint whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Waypoint whereFactionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Waypoint whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Waypoint whereIsUnderConstruction($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Waypoint whereOrbits($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Waypoint whereSymbol($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Waypoint whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Waypoint whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Waypoint whereX($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Waypoint whereY($value)
 *
 * @mixin \Eloquent
 */
class Waypoint extends Model
{
    use FindableBySymbol;

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

    public function getCanRefuelAttribute(): bool
    {
        return $this->traits()
            ->where('symbol', WaypointTraitSymbols::MARKETPLACE)
            ->exists()
            && $this->tradeOpportunities()
                ->where('symbol', TradeSymbols::FUEL)
                ->whereIn('type', [TradeGoodTypes::EXPORT, TradeGoodTypes::EXCHANGE])
                ->exists();
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

    public function scopeCanRefuel(Builder $query): Builder
    {
        return $query->whereRelation('traits', 'symbol', WaypointTraitSymbols::MARKETPLACE)
            ->whereRelation(
                'tradeOpportunities',
                fn (Builder $query) => $query->where('symbol', TradeSymbols::FUEL)
                    ->whereIn('type', [TradeGoodTypes::EXPORT, TradeGoodTypes::EXCHANGE])
            );
    }

    public function scopeBySystem(Builder $query, string $systemSymbol): Builder
    {
        return $query->where('symbol', 'like', $systemSymbol . '-%');
    }

    public function closestRefuelingWaypoint(bool $excludeThis = true): ?static
    {
        $waypoints = Waypoint::canRefuel()
            ->when(
                $excludeThis,
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
