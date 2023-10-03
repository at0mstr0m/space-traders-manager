<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\CrewRotations;
use App\Enums\FlightModes;
use App\Enums\ShipNavStatus;
use App\Enums\ShipRoles;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ship extends Model
{
    protected $with = [
        'modules'
    ];

    protected $fillable = [
        'symbol',
        'role',
        'waypoint_symbol',
        'status',
        'flight_mode',
        'crew_current',
        'crew_capacity',
        'crew_required',
        'crew_rotation',
        'crew_morale',
        'crew_wages',
        'fuel_current',
        'fuel_capacity',
        'fuel_consumed',
        'cooldown',
        'frame_condition',
        'reactor_condition',
        'engine_condition',
        'cargo_capacity',
        'cargo_units',
        'faction_id',   // consider removal
        'frame_id',     // consider removal
        'reactor_id',   // consider removal
        'engine_id',    // consider removal
    ];

    protected $casts = [
        'symbol' => 'string',
        'role' => ShipRoles::class,
        'waypoint_symbol' => 'string',
        'status' => ShipNavStatus::class,
        'flight_mode' => FlightModes::class,
        'crew_current' => 'integer',
        'crew_capacity' => 'integer',
        'crew_required' => 'integer',
        'crew_rotation' => CrewRotations::class,
        'crew_morale' => 'integer',
        'crew_wages' => 'integer',
        'fuel_current' => 'integer',
        'fuel_capacity' => 'integer',
        'fuel_consumed' => 'integer',
        'cooldown' => 'integer',
        'frame_condition' => 'integer',
        'reactor_condition' => 'integer',
        'engine_condition' => 'integer',
        'cargo_capacity' => 'integer',
        'cargo_units' => 'integer',
    ];

    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class);
    }

    public function faction(): BelongsTo
    {
        return $this->belongsTo(Faction::class);
    }

    public function frame(): BelongsTo
    {
        return $this->belongsTo(Frame::class);
    }

    public function reactor(): BelongsTo
    {
        return $this->belongsTo(Reactor::class);
    }

    public function engine(): BelongsTo
    {
        return $this->belongsTo(Engine::class);
    }

    public function modules(): BelongsToMany
    {
        return $this->belongsToMany(Module::class)
            ->using(ShipModule::class)
            ->withPivot(['quantity']);
    }

    public function mounts(): BelongsToMany
    {
        return $this->belongsToMany(Mount::class)
            ->using(ShipMount::class)
            ->withPivot(['quantity']);
    }

    public function cargos(): HasMany
    {
        return $this->hasMany(Cargo::class);
    }
}
