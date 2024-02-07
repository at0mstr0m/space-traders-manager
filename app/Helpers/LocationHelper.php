<?php

declare(strict_types=1);

namespace App\Helpers;

use App\Enums\ShipNavStatus;
use App\Jobs\UpdateShips;
use App\Models\Ship;
use App\Models\Waypoint;
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

    public static function waypointIsInSystem(string $waypointSymbol, string $systemSymbol): bool
    {
        return static::parseSystemSymbol($waypointSymbol) === $systemSymbol;
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

    /**
     * @link https://en.wikipedia.org/wiki/Euclidean_distance
     */
    public static function distance(
        int|string|Waypoint $x1orFirstWaypoint,
        int|string|Waypoint $y1orSecondWaypoint,
        ?int $x2 = null,
        ?int $y2 = null
    ): int {
        return match (true) {
            $x1orFirstWaypoint instanceof Waypoint && $y1orSecondWaypoint instanceof Waypoint => static::distance(
                $x1orFirstWaypoint->x,
                $x1orFirstWaypoint->y,
                $y1orSecondWaypoint->x,
                $y1orSecondWaypoint->y
            ),
            is_string($x1orFirstWaypoint) && is_string($y1orSecondWaypoint) => static::distance(
                Waypoint::findBySymbol($x1orFirstWaypoint),
                Waypoint::findBySymbol($y1orSecondWaypoint)
            ),
            // Euclidean distance is always >= 1
            default => max(
                1,
                (int) round(
                    sqrt(
                        pow($x2 - $x1orFirstWaypoint, 2)
                        + pow($y2 - $y1orSecondWaypoint, 2)
                    )
                )
            ),
        };
    }
}
