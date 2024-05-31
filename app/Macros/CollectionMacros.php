<?php

declare(strict_types=1);

namespace App\Macros;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * @mixin Collection
 */
class CollectionMacros
{
    public function transformKeys(): callable
    {
        return function (callable $callback): static {
            return $this->mapWithKeys(function ($value, $key) use ($callback) {
                return [
                    $callback($key) => is_array($value) ? collect($value)->transformKeys($callback) : $value,
                ];
            });
        };
    }

    public function snakeKeys(): callable
    {
        return function (): static {
            return $this->transformKeys(fn ($item) => Str::snake($item));
        };
    }

    public function hasCount(): callable
    {
        return function (int $count): bool {
            return $this->count() === $count;
        };
    }
}
