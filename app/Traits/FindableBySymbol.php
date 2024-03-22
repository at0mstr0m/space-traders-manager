<?php

declare(strict_types=1);

namespace App\Traits;

trait FindableBySymbol
{
    public static function findBySymbol(string|\UnitEnum $symbol): ?static
    {

        return static::firstWhere('symbol', is_string($symbol) ? $symbol : $symbol->value);
    }
}
