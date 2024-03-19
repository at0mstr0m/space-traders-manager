<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\FrameSymbols;
use App\Traits\FindableBySymbol;
use Illuminate\Support\Carbon;

/**
 * App\Models\Frame.
 *
 * @property int $id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property FrameSymbols $symbol
 * @property string $name
 * @property string $description
 * @property int $module_slots
 * @property int $mounting_points
 * @property int $fuel_capacity
 * @property int $required_power
 * @property int $required_crew
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Ship> $ships
 * @property-read int|null $ships_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Frame newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Frame newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Frame query()
 * @method static \Illuminate\Database\Eloquent\Builder|Frame whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Frame whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Frame whereFuelCapacity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Frame whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Frame whereModuleSlots($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Frame whereMountingPoints($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Frame whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Frame whereRequiredCrew($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Frame whereRequiredPower($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Frame whereSymbol($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Frame whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
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

    public function ships()
    {
        return $this->hasMany(Ship::class);
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
            'symbol' => FrameSymbols::class,
            'name' => 'string',
            'description' => 'string',
            'module_slots' => 'integer',
            'mounting_points' => 'integer',
            'fuel_capacity' => 'integer',
            'required_power' => 'integer',
            'required_crew' => 'integer',
        ];
    }
}
