<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\TradeGoodTypes;
use App\Enums\TradeSymbols;
use App\Enums\WaypointTraitSymbols;
use App\Enums\WaypointTypes;
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
        'type',
        'x',
        'y',
        'faction_id',
        'orbits',
        'is_under_construction',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'symbol' => 'string',
        'type' => WaypointTypes::class,
        'x' => 'integer',
        'y' => 'integer',
        'is_under_construction' => 'boolean',
    ];

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

    public function closestRefuelingStation(string|Waypoint $waypoint): ?Waypoint
    {
        if ($waypoint instanceof Waypoint) {
            $waypoint = $waypoint->symbol;
        }

        $refuelingStations = static::canRefuel()->get();

        if ($refuelingStations->pluck('symbol')->contains($waypoint)) {
            return $waypoint;
        }

        return $this->canRefuel();
    }
}
