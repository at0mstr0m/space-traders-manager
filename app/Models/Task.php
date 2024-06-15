<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\TaskTypes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property TaskTypes $type
 * @property array $payload
 * @property-read FireBaseReference|null $fireBaseReference
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Ship> $ships
 * @property-read int|null $ships_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Task newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Task newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Task query()
 *
 * @mixin \Eloquent
 */
class Task extends Model
{
    protected $fillable = [
        'type',
        'payload',
    ];

    /**
     * The model's default values for attributes.
     *
     * @var array
     */
    protected $attributes = [
        'payload' => '{}',
    ];

    public function ships(): HasMany
    {
        return $this->hasMany(Ship::class);
    }

    public function fireBaseReference(): MorphOne
    {
        return $this->morphOne(FireBaseReference::class, 'model');
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
            'type' => TaskTypes::class,
            'payload' => 'array',
        ];
    }
}
