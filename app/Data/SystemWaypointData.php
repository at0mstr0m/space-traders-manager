<?php

declare(strict_types=1);

namespace App\Data;

use App\Enums\WaypointTypes;
use App\Interfaces\GeneratableFromResponse;
use App\Traits\HasCollectionFromResponse;
use Illuminate\Support\Arr;
use Spatie\LaravelData\Data;

class SystemWaypointData extends Data implements GeneratableFromResponse
{
    use HasCollectionFromResponse;

    public function __construct(
        public string $symbol,
        public string $type,
        public int $x,
        public int $y,
        public array $orbitals,
    ) {
        match (true) {
            !WaypointTypes::isValid($type) => throw new \InvalidArgumentException("Invalid waypoint type: {$type}"),
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
        );
    }
}
