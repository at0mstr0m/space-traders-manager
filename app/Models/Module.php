<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ModuleSymbols;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property ModuleSymbols $symbol
 * @property string $name
 * @property string $description
 * @property int|null $capacity
 * @property int|null $range
 * @property int $required_power
 * @property int $required_crew
 * @property int $required_slots
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Deposit> $deposits
 * @property-read int|null $deposits_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Ship> $ships
 * @property-read int|null $ships_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Module newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Module newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Module query()
 *
 * @mixin \Eloquent
 */
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

    public function ships(): BelongsToMany
    {
        return $this->belongsToMany(Ship::class)
            ->using(ShipModule::class)
            ->withPivot(['quantity']);
    }

    public function deposits(): BelongsToMany
    {
        return $this->belongsToMany(Deposit::class);
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
            'symbol' => ModuleSymbols::class,
            'name' => 'string',
            'description' => 'string',
            'capacity' => 'integer',
            'range' => 'integer',
            'required_power' => 'integer',
            'required_crew' => 'integer',
            'required_slots' => 'integer',
        ];
    }
}
