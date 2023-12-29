<?php

declare(strict_types=1);

namespace App\Data;

use App\Enums\FrameSymbols;
use App\Interfaces\GeneratableFromResponse;
use Spatie\LaravelData\Data;

class FrameData extends Data implements GeneratableFromResponse
{
    public function __construct(
        public string $symbol,
        public string $name,
        public string $description,
        public int $moduleSlots,
        public int $mountingPoints,
        public int $fuelCapacity,
        public int $requiredPower,
        public int $requiredCrew,
    ) {
        if (!FrameSymbols::isValid($symbol)) {
            throw new \InvalidArgumentException("Invalid frame symbol: {$symbol}");
        }
    }

    public static function fromResponse(array $response): static
    {
        return new static(
            symbol: $response['symbol'],
            name: $response['name'],
            description: $response['description'],
            moduleSlots: $response['moduleSlots'],
            mountingPoints: $response['mountingPoints'],
            fuelCapacity: $response['fuelCapacity'],
            requiredPower: $response['requirements']['power'],
            requiredCrew: $response['requirements']['crew'],
        );
    }
}
