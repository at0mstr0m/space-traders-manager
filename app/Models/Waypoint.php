<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\WaypointTypes;
use App\Traits\FindableBySymbol;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
    ];

    protected $casts = [
        'symbol' => 'string',
        'type' => WaypointTypes::class,
        'x' => 'integer',
        'y' => 'integer',
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
