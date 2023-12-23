<?php

declare(strict_types=1);

namespace App\Helpers;

use Illuminate\Support\Str;

class LocationHelper {
    public static function parseLocation(string $waypointSymbol): array
    {
        return explode('-', $waypointSymbol);
    }

    public static function parseSystemSymbol(string $waypointSymbol): string
    {
        return static::parseLocation($waypointSymbol)[0] . '-' . static::parseLocation($waypointSymbol)[1];
    }

    public static function hasLocationPrefix(string $waypointSymbol, string $prefix): bool
    {
        return Str::startsWith(static::parseLocation($waypointSymbol)[2], $prefix);
    }
}
