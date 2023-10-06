<?php

declare(strict_types=1);

namespace App\Data;

use Spatie\LaravelData\Data;
use App\Enums\SystemTypes;
use InvalidArgumentException;
use App\Traits\HasCollectionFromResponse;
use App\Interfaces\GeneratableFromResponse;

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
            !SystemTypes::isValid($type) => throw new InvalidArgumentException("Invalid system type: {$type}"),
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
            distance: $response['distance'],
        );
    }
}
