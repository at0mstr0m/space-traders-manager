<?php

declare(strict_types=1);

namespace App\Helpers;

use App\Data\ConstructionSiteData;
use App\Enums\ShipNavStatus;
use App\Enums\ShipRoles;
use App\Enums\WaypointTraitSymbols;
use App\Exceptions\NoPathException;
use App\Jobs\UpdateShips;
use App\Models\Ship;
use App\Models\Waypoint;
use App\Support\Pathfinding\Dijkstra;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class LocationHelper
{
    public static function parseLocation(string $waypointSymbol): array
    {
        return explode('-', $waypointSymbol);
    }

    public static function parseSystemSymbol(string $waypointSymbol): string
    {
        return static::parseLocation($waypointSymbol)[0]
        . '-'
        . static::parseLocation($waypointSymbol)[1];
    }

    public static function waypointIsInSystem(
        string $waypointSymbol,
        string $systemSymbol
    ): bool {
        return static::parseSystemSymbol($waypointSymbol) === $systemSymbol;
    }

    public static function hasLocationPrefix(
        string $waypointSymbol,
        string $prefix
    ): bool {
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

    public static function marketplacesWithoutSatellite(): EloquentCollection
    {
        $satelliteLocations = Ship::where('role', ShipRoles::SATELLITE)
            ->pluck('waypoint_symbol');

        return Waypoint::whereRelation('traits', 'symbol', WaypointTraitSymbols::MARKETPLACE)
            ->whereNotIn('symbol', $satelliteLocations)
            ->orderBy('symbol')
            ->get();
    }

    public static function getWaypointUnderConstructionInSystem(
        string $systemSymbol
    ): ?ConstructionSiteData {
        /** @var SpaceTraders $api */
        $api = app(SpaceTraders::class);

        $waypointSymbol = Waypoint::bySystem($systemSymbol)
            ->firstWhere('is_under_construction', true)
            ?->symbol;

        return $waypointSymbol
            ? $api->getConstructionSite($waypointSymbol)
            : null;
    }

    public static function getRoutePath(
        string|Waypoint $origin,
        string|Waypoint $destination,
        int $fuelCapacity
    ): ?array {
        $origin = is_string($origin) ? $origin : $origin->symbol;
        $destination = is_string($destination) ? $destination : $destination->symbol;

        $systemSymbols = [
            $originSystemSymbol,
            $destinationWaypointSymbol
        ] = [
            static::parseSystemSymbol($origin),
            static::parseSystemSymbol($destination),
        ];

        $cacheKey = ((string) $fuelCapacity)
            . ':'
            . implode(':', Arr::sort($systemSymbols));

        $graph = Cache::tags(['graphs'])
            ->rememberForever(
                $cacheKey,
                fn () => static::buildGraph(
                    $fuelCapacity,
                    $originSystemSymbol,
                    $destinationWaypointSymbol
                )
            );

        try {
            return $graph->findShortestPath($origin, $destination);
        } catch (NoPathException $th) {
            return null;
        }
    }

    private static function buildGraph(
        int $fuelCapacity,
        string $originSystemSymbol,
        string $destinationWaypointSymbol
    ): Dijkstra {
        $graph = new Dijkstra();

        if ($originSystemSymbol !== $destinationWaypointSymbol) {
        }

        Waypoint::onlyCanRefuel()
            ->get()
            ->pipe(fn (EloquentCollection $waypoints) => $waypoints->crossJoin($waypoints))
            ->reject(fn (array $waypoints) => $waypoints[0]->symbol === $waypoints[1]->symbol)
            ->map(fn (array $waypoints) => [
                'origin' => $waypoints[0]->symbol,
                'destination' => $waypoints[1]->symbol,
                'distance' => LocationHelper::distance($waypoints[0]->symbol, $waypoints[1]->symbol),
            ])
            ->values()
            ->each(function (array $waypoint) use (&$graph, $fuelCapacity) {
                if ($waypoint['distance'] > $fuelCapacity) {
                    return;
                }

                $graph->addEdge(
                    $waypoint['origin'],
                    $waypoint['destination'],
                    $waypoint['distance']
                );
            });

        return $graph;
    }
}
