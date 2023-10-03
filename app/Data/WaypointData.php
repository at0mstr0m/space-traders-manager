<?php

namespace App\Data;

use Illuminate\Support\Arr;
use App\Enums\WaypointTypes;
use Spatie\LaravelData\Data;
use App\Enums\FactionSymbols;
use InvalidArgumentException;
use Spatie\LaravelData\DataCollection;
use App\Interfaces\GeneratableFromResponse;
use Spatie\LaravelData\Attributes\DataCollectionOf;

class WaypointData extends Data implements GeneratableFromResponse
{
    public function __construct(
        public string $symbol,
        public string $type,
        public string $x,
        public string $y,
        public string $faction,
        #[DataCollectionOf(WaypointOrbitalData::class)]
        public ?DataCollection $orbitals = null,
        #[DataCollectionOf(WaypointTraitData::class)]
        public ?DataCollection $traits = null,
        public ?string $orbits,
    ) {
        match (true) {
            !WaypointTypes::isValid($type) => throw new InvalidArgumentException("Invalid waypoint type: {$type}"),
            !FactionSymbols::isValid($faction) => throw new InvalidArgumentException("Invalid faction symbol: {$faction}"),
            default => null,
        };
    }

    public static function fromResponse(array $response): static
    {
        return new self(
            symbol: $response['symbol'],
            type: $response['type'],
            x: $response['x'],
            y: $response['y'],
            faction: $response['faction']['symbol'],
            orbitals: WaypointOrbitalData::collectionFromResponse($response['orbitals']),
            traits: WaypointTraitData::collectionFromResponse($response['traits']),
            orbits: data_get($response, 'orbits'),
        );
    }
}
