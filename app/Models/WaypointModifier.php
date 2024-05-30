<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\WaypointModifierSymbols;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property WaypointModifierSymbols $symbol
 * @property string $name
 * @property string $description
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Waypoint> $waypoints
 * @property-read int|null $waypoints_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder|WaypointModifier newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|WaypointModifier newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|WaypointModifier query()
 *
 * @mixin \Eloquent
 */
class WaypointModifier extends Model
{
    protected $fillable = [
        'symbol',
        'name',
        'description',
    ];

    public function waypoints(): BelongsToMany
    {
        return $this->belongsToMany(Waypoint::class);
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
            'symbol' => WaypointModifierSymbols::class,
            'name' => 'string',
            'description' => 'string',
        ];
    }
}
