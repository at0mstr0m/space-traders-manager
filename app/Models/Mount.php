<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\MountSymbols;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Mount extends Model
{
    protected $with = [
        'deposits',
    ];

    protected $fillable = [
        'symbol',
        'name',
        'description',
        'strength',
        'required_power',
        'required_crew',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'symbol' => MountSymbols::class,
        'name' => 'string',
        'description' => 'string',
        'strength' => 'integer',
        'required_power' => 'integer',
        'required_crew' => 'integer',
    ];

    public function ships(): BelongsToMany
    {
        return $this->belongsToMany(Ship::class)
            ->using(ShipMount::class)
            ->withPivot(['quantity']);
    }

    public function deposits(): BelongsToMany
    {
        return $this->belongsToMany(Deposit::class);
    }
}
