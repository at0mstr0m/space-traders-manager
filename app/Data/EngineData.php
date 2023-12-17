<?php

declare(strict_types=1);

namespace App\Data;

use App\Enums\EngineSymbols;
use Spatie\LaravelData\Data;
use InvalidArgumentException;
use App\Interfaces\GeneratableFromResponse;

class EngineData extends Data implements GeneratableFromResponse
{
    public function __construct(
        public string $symbol,
        public string $name,
        public string $description,
        public int $speed,
        public int $requiredPower,
        public int $requiredCrew,
    ) {
        if (!EngineSymbols::isValid($symbol)) {
            throw new InvalidArgumentException("Invalid engine symbol: {$symbol}");
        }
    }

    public static function fromResponse(array $response): static
    {
        return new static(
            symbol: $response['symbol'],
            name: $response['name'],
            description: $response['description'],
            speed: $response['speed'],
            requiredPower: $response['requirements']['power'],
            requiredCrew: $response['requirements']['crew'],
        );
    }
}
