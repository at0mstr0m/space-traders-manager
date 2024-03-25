<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\FactionTraits;
use Illuminate\Support\Carbon;

/**
 * App\Models\FactionTrait.
 *
 * @property int $id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property FactionTraits $symbol
 * @property string $name
 * @property string $description
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Faction> $faction
 * @property-read int|null $faction_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder|FactionTrait newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FactionTrait newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FactionTrait query()
 * @method static \Illuminate\Database\Eloquent\Builder|FactionTrait whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FactionTrait whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FactionTrait whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FactionTrait whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FactionTrait whereSymbol($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FactionTrait whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class FactionTrait extends Model
{
    protected $fillable = [
        'symbol',
        'name',
        'description',
    ];

    public function faction()
    {
        return $this->belongsToMany(Faction::class);
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
            'symbol' => FactionTraits::class,
            'name' => 'string',
            'description' => 'string',
        ];
    }
}
