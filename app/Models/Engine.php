<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\EngineSymbols;
use App\Traits\FindableBySymbol;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Engine extends Model
{
    use FindableBySymbol;

    protected $fillable = [
        'symbol',
        'name',
        'description',
        'speed',
        'required_power',
        'required_crew',
    ];

    protected $casts = [
        'symbol' => EngineSymbols::class,
        'name' => 'string',
        'description' => 'string',
        'power_output' => 'integer',
        'speed' => 'integer',
        'required_power' => 'integer',
        'required_crew' => 'integer',
    ];

    public function ships(): HasMany
    {
        return $this->hasMany(Ship::class);
    }
}
