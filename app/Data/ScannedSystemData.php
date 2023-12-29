<?php

declare(strict_types=1);

namespace App\Data;

use App\Enums\SystemTypes;
use App\Interfaces\GeneratableFromResponse;
use App\Traits\HasCollectionFromResponse;
use Spatie\LaravelData\Data;

class ScannedSystemData extends Data implements GeneratableFromResponse
{
    use HasCollectionFromResponse;

    public function __construct(
        public string $symbol,
        public string $type,
        public int $x,
        public int $y,
        public int $distance,
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
            distance: $response['distance'],
        );
    }
}
