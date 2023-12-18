<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Model as EloquentModel;

class Model extends EloquentModel
{
    public function fillAndSave(Arrayable|array $attributes): bool
    {
        if (!is_array($attributes)) {
            $attributes = $attributes->toArray();
        }

        return $this->fill($attributes)->save();
    }

    public static function new(Arrayable|array $attributes = []): static
    {
        if (!is_array($attributes)) {
            $attributes = $attributes->toArray();
        }

        return new static($attributes);
    }
}
