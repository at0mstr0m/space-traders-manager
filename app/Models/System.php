<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\SystemTypes;
use App\Traits\FindableBySymbol;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string $symbol
 * @property string $sector_symbol
 * @property SystemTypes $type
 * @property int $x
 * @property int $y
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Faction> $factions
 * @property-read int|null $factions_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Ship> $ships
 * @property-read int|null $ships_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Waypoint> $waypoints
 * @property-read int|null $waypoints_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder|System newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|System newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|System query()
 * @method static \Illuminate\Database\Eloquent\Builder|System searchBySymbol(string $search = '')
 *
 * @mixin \Eloquent
 */
class System extends Model
{
    use FindableBySymbol;

    protected $fillable = [
        'symbol',
        'sector_symbol',
        'type',
        'x',
        'y',
    ];

    public function waypoints(): HasMany
    {
        return $this->hasMany(Waypoint::class, 'system_symbol', 'symbol');
    }

    public function factions(): BelongsToMany
    {
        return $this->belongsToMany(Faction::class);
    }

    public function ships(): HasManyThrough
    {
        return $this->hasManyThrough(
            Ship::class,
            Waypoint::class,
            'system_symbol',
            'waypoint_symbol',
            'symbol',
            'symbol'
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
            'symbol' => 'string',
            'sector_symbol' => 'string',
            'type' => SystemTypes::class,
            'x' => 'int',
            'y' => 'int',
        ];
    }
}
