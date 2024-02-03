<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\FrameSymbols;
use App\Traits\FindableBySymbol;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Frame extends Model
{
    use FindableBySymbol;

    protected $fillable = [
        'symbol',
        'name',
        'description',
        'module_slots',
        'mounting_points',
        'fuel_capacity',
        'required_power',
        'required_crew',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'symbol' => FrameSymbols::class,
        'name' => 'string',
        'description' => 'string',
        'module_slots' => 'integer',
        'mounting_points' => 'integer',
        'fuel_capacity' => 'integer',
        'required_power' => 'integer',
        'required_crew' => 'integer',
    ];

    public function ships()
    {
        return $this->hasMany(Ship::class);
    }
}
