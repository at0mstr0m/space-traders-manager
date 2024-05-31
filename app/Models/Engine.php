<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\EngineSymbols;
use App\Traits\FindableBySymbol;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property EngineSymbols $symbol
 * @property string $name
 * @property string $description
 * @property int $speed
 * @property int $required_power
 * @property int $required_crew
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Ship> $ships
 * @property-read int|null $ships_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Engine newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Engine newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Engine query()
 * @method static \Illuminate\Database\Eloquent\Builder|Engine searchBySymbol(string $search = '')
 *
 * @mixin \Eloquent
 */
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

    public function ships(): HasMany
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
            'symbol' => EngineSymbols::class,
            'name' => 'string',
            'description' => 'string',
            'power_output' => 'integer',
            'speed' => 'integer',
            'required_power' => 'integer',
            'required_crew' => 'integer',
        ];
    }
}
