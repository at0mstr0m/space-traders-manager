<?php

declare(strict_types=1);

namespace App\Traits;

trait FindableBySymbol
{
    public static function findBySymbol(string $symbol): ?static
    {
        return static::firstWhere('symbol', $symbol);
    }
}
