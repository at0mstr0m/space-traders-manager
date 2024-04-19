<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\SystemTypes;
use App\Traits\FindableBySymbol;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

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
