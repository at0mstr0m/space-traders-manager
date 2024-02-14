<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\WaypointTypes;
use App\Traits\FindableBySymbol;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * App\Models\Waypoint
 *
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string $symbol
 * @property WaypointTypes $type
 * @property int $faction_id
 * @property int $x
 * @property int $y
 * @property Waypoint|null $orbits
 * @property bool|null $is_under_construction
 * @property-read \App\Models\Faction $faction
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\WaypointModifier> $modifiers
 * @property-read int|null $modifiers_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Waypoint> $orbitals
 * @property-read int|null $orbitals_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\WaypointTrait> $traits
 * @property-read int|null $traits_count
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

    public function orbits(): BelongsTo
    {
        return $this->belongsTo(static::class, 'orbits', 'symbol');
    }

    public function orbitals(): HasMany
    {
        return $this->hasMany(static::class, 'symbol', 'orbits');
    }
}
