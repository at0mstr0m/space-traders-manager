<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ReactorSymbols;
use App\Traits\FindableBySymbol;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * App\Models\Reactor.
 *
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
 * @method static \Illuminate\Database\Eloquent\Builder|Reactor whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Reactor whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Reactor whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Reactor whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Reactor wherePowerOutput($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Reactor whereRequiredCrew($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Reactor whereSymbol($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Reactor whereUpdatedAt($value)
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

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
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
