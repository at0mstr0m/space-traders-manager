<?php

declare(strict_types=1);

namespace App\Data;

use App\Enums\ModuleSymbols;
use Spatie\LaravelData\Data;
use InvalidArgumentException;
use App\Traits\HasCollectionFromResponse;
use App\Interfaces\GeneratableFromResponse;

class ModuleData extends Data implements GeneratableFromResponse
{
    use HasCollectionFromResponse;

    public function __construct(
        public string $symbol,
        public string $name,
        public string $description,
        public int $requiredPower,
        public int $requiredCrew,
        public int $requiredSlots,
        public ?int $capacity = null,
        public ?int $range = null,
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
