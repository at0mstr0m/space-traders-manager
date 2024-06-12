<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Database\Eloquent\Prunable;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string $model_type
 * @property int $model_id
 * @property string $key
 * @property-read \Eloquent|\Illuminate\Database\Eloquent\Model $model
 *
 * @method static \Illuminate\Database\Eloquent\Builder|FireBaseReference newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FireBaseReference newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FireBaseReference query()
 *
 * @mixin \Eloquent
 */
class FireBaseReference extends Model
{
    use Prunable;

    protected $fillable = [
        'key',
    ];

    public function model(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the prunable model query.
     */
    public function prunable(): Builder
    {
        return static::doesntHave(
            'model',
            callback: fn (Builder $query) => $query->withoutGlobalScope(SoftDeletingScope::class)
        );
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'key' => 'string',
        ];
    }
}
