<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\WaypointTraitSymbols;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class WaypointTrait extends Model
{
    protected $fillable = [
        'symbol',
        'name',
        'description',
    ];

    protected $casts = [
        'symbol' => WaypointTraitSymbols::class,
        'name' => 'string',
        'description' => 'string',
    ];

    public function waypoints(): BelongsToMany
    {
        return $this->belongsToMany(Waypoint::class);
    }
}
