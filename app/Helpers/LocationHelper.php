<?php

declare(strict_types=1);

namespace App\Helpers;

use App\Enums\ShipNavStatus;
use App\Jobs\UpdateShips;
use App\Models\Ship;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class LocationHelper
{
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

    public static function observableWaypoints()
    {
        UpdateShips::dispatchSync();

        return Ship::getQuery()
            ->whereNot('status', ShipNavStatus::IN_TRANSIT)
            ->select('waypoint_symbol')
            ->get()
            ->pluck('waypoint_symbol')
            ->unique();
    }

    public static function systemsWithShips(): Collection
    {
        UpdateShips::dispatchSync();

        return Ship::getQuery()
            ->select('waypoint_symbol')
            ->get()
            ->pluck('waypoint_symbol')
            ->map(fn (string $waypointSymbol) => static::parseSystemSymbol($waypointSymbol))
            ->unique();
    }
}
