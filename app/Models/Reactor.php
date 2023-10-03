<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ReactorSymbols;
use App\Traits\FindableBySymbol;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Reactor extends Model
{
    use FindableBySymbol;

    protected $fillable = [
        'symbol',
        'name',
        'description',
        'power_output',
        'required_crew',
    ];

    protected $casts = [
        'symbol' => ReactorSymbols::class,
        'name' => 'string',
        'description' => 'string',
        'power_output' => 'integer',
        'required_crew' => 'integer',
    ];

    public function ships(): HasMany
    {
        return $this->hasMany(Ship::class);
    }
}
