<?php

declare(strict_types=1);

namespace App\Helpers;

class LocationHelper {
    public static function parseLocation(string $waypointSymbol): array
    {
        return explode('-', $waypointSymbol);
    }

    public static function parseSystemSymbol(string $waypointSymbol): string
    {
        return self::parseLocation($waypointSymbol)[0] . '-' . self::parseLocation($waypointSymbol)[1];
    }
}
