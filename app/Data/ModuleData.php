<?php

namespace App\Data;

use App\Enums\ModuleSymbols;
use Spatie\LaravelData\Data;
use InvalidArgumentException;
use App\Interfaces\GeneratableFromResponse;

class ModuleData extends Data implements GeneratableFromResponse
{
    public function __construct(
        public string $symbol,
        public string $name,
        public string $description,
        public ?int $capacity = null,
        public ?int $range = null,
        public int $requiredPower,
        public int $requiredCrew,
        public int $requiredSlots,
    ) {
        if (!ModuleSymbols::isValid($symbol)) {
            throw new InvalidArgumentException("Invalid module symbol: {$symbol}");
        }
    }

    public static function fromResponse(array $response): static
    {
        return new self(
            symbol: $response['symbol'],
            name: $response['name'],
            description: $response['description'],
            capacity: $response['capacity'] ?? null,
            range: $response['range'] ?? null,
            requiredPower: $response['requirements']['power'],
            requiredCrew: $response['requirements']['crew'],
            requiredSlots: $response['requirements']['slots'],
        );
    }
}
