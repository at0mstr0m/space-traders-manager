<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Model as EloquentModel;

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
