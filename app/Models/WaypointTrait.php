<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\WaypointTraitSymbols;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Carbon;

/**
 * App\Models\WaypointTrait.
 *
 * @property int $id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property WaypointTraitSymbols $symbol
 * @property string $name
 * @property string $description
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Waypoint> $waypoints
 * @property-read int|null $waypoints_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder|WaypointTrait newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|WaypointTrait newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|WaypointTrait query()
 * @method static \Illuminate\Database\Eloquent\Builder|WaypointTrait whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WaypointTrait whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WaypointTrait whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WaypointTrait whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WaypointTrait whereSymbol($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WaypointTrait whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class WaypointTrait extends Model
{
    protected $fillable = [
        'symbol',
        'name',
        'description',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'symbol' => WaypointTraitSymbols::class,
        'name' => 'string',
        'description' => 'string',
    ];

    public function waypoints(): BelongsToMany
    {
        return $this->belongsToMany(Waypoint::class);
    }
}
