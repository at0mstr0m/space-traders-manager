<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Model as EloquentModel;

/**
 * App\Models\Model.
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Model newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Model newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Model query()
 *
 * @mixin \Eloquent
 */
class Model extends EloquentModel
{
    public static function new(array|Arrayable $attributes = []): static
    {
        if (!is_array($attributes)) {
            $attributes = $attributes->toArray();
        }

        return new static($attributes);
    }

    public function fillAndSave(array|Arrayable $attributes): bool
    {
        if (!is_array($attributes)) {
            $attributes = $attributes->toArray();
        }

        return $this->fill($attributes)->save();
    }

    public function pipeSave(): static
    {
        $this->save();

        return $this;
    }
}
