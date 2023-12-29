<?php

declare(strict_types=1);

namespace App\Data;

use App\Enums\SystemTypes;
use App\Interfaces\GeneratableFromResponse;
use App\Traits\HasCollectionFromResponse;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;

class SystemData extends Data implements GeneratableFromResponse
{
    use HasCollectionFromResponse;

    public function __construct(
        public string $symbol,
        public string $type,
        public int $x,
        public int $y,
        #[DataCollectionOf(SystemWaypointData::class)]
        public ?DataCollection $waypoints = null,
        public array $factions,
    ) {
        match (true) {
            !SystemTypes::isValid($type) => throw new \InvalidArgumentException("Invalid system type: {$type}"),
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
            waypoints: SystemWaypointData::collectionFromResponse($response['waypoints']),
            factions: $response['factions'],
        );
    }
}
