<?php

declare(strict_types=1);

namespace App\Helpers;

use App\Data\ConstructionSiteData;
use App\Enums\ShipNavStatus;
use App\Enums\ShipRoles;
use App\Enums\WaypointTraitSymbols;
use App\Exceptions\NoPathException;
use App\Jobs\UpdateShips;
use App\Models\Agent;
use App\Models\Ship;
use App\Models\System;
use App\Models\Waypoint;
use App\Support\Pathfinding\Dijkstra;
use Illuminate\Contracts\Database\Eloquent\Builder;
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

    public static function isSystemSymbol(string $symbol): bool
    {
        return count(static::parseLocation($symbol)) === 2;
    }

    public static function isWaypointSymbol(string $symbol): bool
    {
        return count(static::parseLocation($symbol)) === 3;
    }

    public static function observableWaypoints(): Collection
    {
        UpdateShips::dispatchSync();

        return Ship::getQuery()
            ->whereNot('status', ShipNavStatus::IN_TRANSIT)
            ->select('waypoint_symbol')
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
        int|string|System|Waypoint $x1orFirst,
        int|string|System|Waypoint $y1orSecond,
        ?int $x2 = null,
        ?int $y2 = null
    ): int {
        $bothAreWaypoints = $x1orFirst instanceof Waypoint && $y1orSecond instanceof Waypoint;
        $bothAreStrings = is_string($x1orFirst) && is_string($y1orSecond);
        // check if both waypoints are in same system. if not return 0
        if ($bothAreWaypoints || $bothAreStrings) {
            $firstSystem = $bothAreStrings
                ? static::parseSystemSymbol($x1orFirst)
                : $x1orFirst->system_symbol;
            $secondSystem = $bothAreStrings
                ? static::parseSystemSymbol($y1orSecond)
                : $y1orSecond->system_symbol;

            if ($firstSystem !== $secondSystem) {
                return 0;
            }
        }

        return match (true) {
            $bothAreWaypoints
                || ($x1orFirst instanceof System && $y1orSecond instanceof System) => static::distance(
                    $x1orFirst->x,
                    $x1orFirst->y,
                    $y1orSecond->x,
                    $y1orSecond->y
                ),
            $bothAreStrings => match (true) {
                static::isSystemSymbol($x1orFirst)
                    && static::isSystemSymbol($y1orSecond) => static::distance(
                        System::findBySymbol($x1orFirst),
                        System::findBySymbol($y1orSecond)
                    ),
                static::isWaypointSymbol($x1orFirst)
                    && static::isWaypointSymbol($y1orSecond) => static::distance(
                        Waypoint::findBySymbol($x1orFirst),
                        Waypoint::findBySymbol($y1orSecond)
                    ),
            },
            // Euclidean distance is always >= 1
            default => max(
                1,
                (int) round(
                    sqrt(
                        pow($x2 - $x1orFirst, 2)
                        + pow($y2 - $y1orSecond, 2)
                    )
                )
            ),
        };
    }

    public static function marketplacesWithoutSatellite(
        bool $onlyHeadquarter = true
    ): EloquentCollection {
        $satelliteLocations = Ship::where('role', ShipRoles::SATELLITE)
            ->pluck('waypoint_symbol');

        return Waypoint::whereRelation('traits', 'symbol', WaypointTraitSymbols::MARKETPLACE)
            ->whereNotIn('symbol', $satelliteLocations)
            ->when(
                $onlyHeadquarter,
                fn (Builder $query) => $query->where(
                    'system_symbol',
                    Agent::first()->starting_system->symbol
                )
            )
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
    ): null|array|bool {
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

        if ($graph === false) {
            return false;
        }

        try {
            return $graph->findShortestPath($origin, $destination);
        } catch (NoPathException $th) {
            return null;
        }
    }

    private static function buildGraph(
        int $fuelCapacity,
        string $originSystemSymbol,
        string $destinationSystemSymbol
    ): bool|Dijkstra {
        if ($originSystemSymbol === $destinationSystemSymbol) {
            $graph = new Dijkstra();
            static::buildSystemGraph($graph, $fuelCapacity, $originSystemSymbol);

            return $graph;
        }

        $graph = Cache::tags(['graphs'])
            ->remember(
                'system_connections_graph',
                now()->addHour(),
                function () {
                    $graph = new Dijkstra();

                    System::whereHas('connections')
                        ->get()
                        ->each(function (System $system) use (&$graph) {
                            $jumpGateSymbol = $system->jumpGate->symbol;
                            System::findManyBySymbol(
                                $system->connections()->pluck('symbol')
                            )->each(
                                fn (System $connectedSystem) => $graph->addEdge(
                                    $jumpGateSymbol,
                                    $connectedSystem->jumpGate->symbol,
                                    LocationHelper::distance(
                                        $system,
                                        $connectedSystem
                                    ),
                                    true
                                )
                            );
                        });

                    return $graph;
                }
            );

        $systems = System::findManyBySymbol([
            $originSystemSymbol,
            $destinationSystemSymbol,
        ]);

        // JumpGates in origin and destination System must be connected
        $jumpGates = $systems->pluck('jumpGate');
        if ($jumpGates->filter()->count() !== 2) {
            return false;
        }

        try {
            $graph->findShortestPath(
                $jumpGates->get(0)->symbol,
                $jumpGates->get(1)->symbol,
            );
        } catch (NoPathException $th) {
            return false;
        }

        $systems->each(fn (System $system) => static::buildSystemGraph(
            $graph,
            $fuelCapacity,
            $system
        ));

        return $graph;
    }

    private static function buildSystemGraph(
        Dijkstra &$graph,
        int $fuelCapacity,
        string|System $system
    ): void {
        (is_string($system) ? System::findBySymbol($system) : $system)
            ->waypoints()
            ->onlyCanRefuel()
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
    }
}
