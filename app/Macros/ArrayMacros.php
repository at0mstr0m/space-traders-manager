<?php

declare(strict_types=1);

namespace App\Macros;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;

/**
 * @mixin Arr
 */
class ArrayMacros
{
    public function transformKeys(): callable
    {
        return function (array $item, callable $callback): array {
            return static::mapWithKeys($item, function ($value, $key) use ($callback) {
                return [
                    $callback($key) => is_array($value) ? static::transformKeys($value, $callback) : $value,
                ];
            });
        };
    }

    public function snakeKeys(): callable
    {
        return function (array $item): array {
            return static::transformKeys($item, fn ($item) => Str::snake($item));
        };
    }
}
