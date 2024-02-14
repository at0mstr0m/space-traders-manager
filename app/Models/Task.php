<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\TaskTypes;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * App\Models\Task
 *
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property TaskTypes $type
 * @property array $payload
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Ship> $ships
 * @property-read int|null $ships_count
 * @method static \Illuminate\Database\Eloquent\Builder|Task newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Task newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Task query()
 * @method static \Illuminate\Database\Eloquent\Builder|Task whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Task whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Task wherePayload($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Task whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Task whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Task extends Model
{
    protected $fillable = [
        'type',
        'payload',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'type' => TaskTypes::class,
        'payload' => 'json',
    ];

    public function ships(): HasMany
    {
        return $this->hasMany(Ship::class);
    }
}
