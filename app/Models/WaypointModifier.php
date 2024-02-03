<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\WaypointModifierSymbols;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

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
