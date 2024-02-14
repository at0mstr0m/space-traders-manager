<?php

declare(strict_types=1);

namespace App\Data;

use App\Enums\FactionSymbols;
use App\Enums\WaypointTypes;
use App\Interfaces\GeneratableFromResponse;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;

class WaypointData extends Data implements GeneratableFromResponse
{
    public function __construct(
        public string $symbol,
        public string $type,
        public int $x,
        public int $y,
        public string $faction,
        #[DataCollectionOf(WaypointOrbitalData::class)]
        public ?DataCollection $orbitals = null,
        #[DataCollectionOf(WaypointTraitData::class)]
        public ?DataCollection $traits = null,
        #[DataCollectionOf(WaypointModifierData::class)]
        public ?DataCollection $modifiers = null,
        public ?string $orbits = null,
        public ?bool $isUnderConstruction = null,
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
            faction: $response['faction']['symbol'],
            orbitals: WaypointOrbitalData::collectionFromResponse($response['orbitals']),
            traits: WaypointTraitData::collectionFromResponse($response['traits']),
            modifiers: WaypointModifierData::collectionFromResponse($response['modifiers']),
            orbits: data_get($response, 'orbits'),
            isUnderConstruction: data_get($response, 'isUnderConstruction'),
        );
    }
}
