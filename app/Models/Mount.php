<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\MountSymbols;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property MountSymbols $symbol
 * @property string $name
 * @property string $description
 * @property int|null $strength
 * @property int $required_power
 * @property int $required_crew
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Deposit> $deposits
 * @property-read int|null $deposits_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Ship> $ships
 * @property-read int|null $ships_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Mount newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Mount newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Mount query()
 *
 * @mixin \Eloquent
 */
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
            'symbol' => MountSymbols::class,
            'name' => 'string',
            'description' => 'string',
            'strength' => 'integer',
            'required_power' => 'integer',
            'required_crew' => 'integer',
        ];
    }
}
