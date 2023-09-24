<?php

declare(strict_types=1);

namespace App\Helpers;

class LocationHelper {


    public static function parseLocation(string $waypointSymbol): array
    {
        return explode('-', $waypointSymbol);
    }
}
