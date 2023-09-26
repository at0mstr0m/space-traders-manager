<?php

declare(strict_types=1);

namespace App\Traits;

trait FindableBySymbol
{
    public static function findBySymbol(string $symbol): ?self
    {
        return self::firstWhere('symbol', $symbol);
    }
}
