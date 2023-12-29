<?php

declare(strict_types=1);

namespace App\Data;

use App\Enums\ReactorSymbols;
use App\Interfaces\GeneratableFromResponse;
use Spatie\LaravelData\Data;

class ReactorData extends Data implements GeneratableFromResponse
{
    public function __construct(
        public string $symbol,
        public string $name,
        public string $description,
        public int $powerOutput,
        public int $requiredCrew,
    ) {
        if (!ReactorSymbols::isValid($symbol)) {
            throw new \InvalidArgumentException("Invalid reactor symbol: {$symbol}");
        }
    }

    public static function fromResponse(array $response): static
    {
        return new static(
            symbol: $response['symbol'],
            name: $response['name'],
            description: $response['description'],
            powerOutput: $response['powerOutput'],
            requiredCrew: $response['requirements']['crew'],
        );
    }
}
