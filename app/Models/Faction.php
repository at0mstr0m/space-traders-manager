<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\FactionSymbols;
use App\Traits\FindableBySymbol;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property FactionSymbols $symbol
 * @property string $name
 * @property string $description
 * @property string $headquarters
 * @property bool $is_recruiting
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Ship> $ships
 * @property-read int|null $ships_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\FactionTrait> $traits
 * @property-read int|null $traits_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Faction newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Faction newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Faction query()
 * @method static \Illuminate\Database\Eloquent\Builder|Faction searchBySymbol(string $search = '')
 * @method static \Database\Factories\FactionFactory factory($count = null, $state = [])
 *
 * @mixin \Eloquent
 */
class Faction extends Model
{
    use FindableBySymbol;
    use HasFactory;

    protected $fillable = [
        'symbol',
        'name',
        'headquarters',
        'description',
        'is_recruiting',
    ];

    public function ships(): BelongsToMany
    {
        return $this->belongsToMany(Ship::class);
    }

    public function traits(): BelongsToMany
    {
        return $this->belongsToMany(FactionTrait::class);
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
            'symbol' => FactionSymbols::class,
            'name' => 'string',
            'headquarters' => 'string',
            'description' => 'string',
            'is_recruiting' => 'boolean',
        ];
    }
}
