<?php

declare(strict_types=1);

namespace App\Data;

use App\Enums\FactionSymbols;
use App\Enums\WaypointTypes;
use App\Interfaces\GeneratableFromResponse;
use App\Traits\HasCollectionFromResponse;
use Illuminate\Support\Arr;
use Spatie\LaravelData\Data;

class ScannedWaypointData extends Data implements GeneratableFromResponse
{
    use HasCollectionFromResponse;

    public function __construct(
        public string $symbol,
        public string $type,
        public int $x,
        public int $y,
        public array $orbitals,
        public string $faction,
        public array $traits,
        public ChartData $chart,
        public ?string $orbits,
    ) {
        match (true) {
            !WaypointTypes::isValid($type) => throw new \InvalidArgumentException("Invalid waypoint type: {$type}"),
            !FactionSymbols::isValid($faction) => throw new \InvalidArgumentException("Invalid faction symbol: {$faction}"),
            default => null,
        };
    }

    public static function fromResponse(array $response): static
    {
        return new static(
            symbol: $response['symbol'],
            type: $response['type'],
            x: $response['x'],
            y: $response['y'],
            orbitals: Arr::map($response['orbitals'], fn (array $orbital) => $orbital['symbol']),
            faction: $response['faction']['symbol'],
            traits: Arr::map($response['traits'], fn (array $orbital) => $orbital['symbol']),
            chart: ChartData::fromResponse($response['chart']),
            orbits: data_get($response, 'orbits'),
        );
    }
}
