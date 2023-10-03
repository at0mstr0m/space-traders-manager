<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ModuleSymbols;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Module extends Model
{
    protected $fillable = [
        'symbol',
        'name',
        'description',
        'capacity',
        'range',
        'required_power',
        'required_crew',
        'required_slots',
    ];

    protected $casts = [
        'symbol' => ModuleSymbols::class,
        'name' => 'string',
        'description' => 'string',
        'capacity' => 'integer',
        'range' => 'integer',
        'required_power' => 'integer',
        'required_crew' => 'integer',
        'required_slots' => 'integer',
    ];

    public function ships(): BelongsToMany
    {
        return $this->belongsToMany(Ship::class)
            ->using(ShipModule::class)
            ->withPivot(['quantity']);
    }

    public function deposits(): BelongsToMany
    {
        return $this->belongsToMany(Deposits::class);
    }
}
