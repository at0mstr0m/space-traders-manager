<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\WaypointModifierSymbols;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * App\Models\WaypointModifier
 *
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property WaypointModifierSymbols $symbol
 * @property string $name
 * @property string $description
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Waypoint> $waypoints
 * @property-read int|null $waypoints_count
 * @method static \Illuminate\Database\Eloquent\Builder|WaypointModifier newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|WaypointModifier newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|WaypointModifier query()
 * @method static \Illuminate\Database\Eloquent\Builder|WaypointModifier whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WaypointModifier whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WaypointModifier whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WaypointModifier whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WaypointModifier whereSymbol($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WaypointModifier whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class WaypointModifier extends Model
{
    protected $fillable = [
        'symbol',
        'name',
        'description',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'symbol' => WaypointModifierSymbols::class,
        'name' => 'string',
        'description' => 'string',
    ];

    public function waypoints(): BelongsToMany
    {
        return $this->belongsToMany(Waypoint::class);
    }
}
