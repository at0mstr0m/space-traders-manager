<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ReactorSymbols;
use App\Traits\FindableBySymbol;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property ReactorSymbols $symbol
 * @property string $name
 * @property string $description
 * @property int $power_output
 * @property int $required_crew
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Ship> $ships
 * @property-read int|null $ships_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Reactor newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Reactor newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Reactor query()
 * @method static \Illuminate\Database\Eloquent\Builder|Reactor searchBySymbol(string $search = '')
 *
 * @mixin \Eloquent
 */
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
            'symbol' => ReactorSymbols::class,
            'name' => 'string',
            'description' => 'string',
            'power_output' => 'integer',
            'required_crew' => 'integer',
        ];
    }
}
