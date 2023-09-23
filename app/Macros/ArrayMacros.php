<?php

declare(strict_types=1);

namespace App\Macros;

use Illuminate\Support\Str;

class ArrayMacros
{
    public function transformKeys(): callable
    {
        return function (array $item, callable $callback): array {
            return self::mapWithKeys($item, function ($value, $key) use ($callback) {
                return [
                    $callback($key) => is_array($value) ? self::transformKeys($value, $callback) : $value,
                ];
            });
        };
    }

    public function snakeKeys(): callable
    {
        return function (array $item): array {
            return self::transformKeys($item, fn ($item) => Str::snake($item));
        };
    }
}
