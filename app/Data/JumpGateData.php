<?php

declare(strict_types=1);

namespace App\Data;

use Spatie\LaravelData\Data;
use App\Enums\FactionSymbols;
use InvalidArgumentException;
use Spatie\LaravelData\DataCollection;
use App\Interfaces\GeneratableFromResponse;
use Spatie\LaravelData\Attributes\DataCollectionOf;

class JumpGateData extends Data implements GeneratableFromResponse
{
    public function __construct(
        public int $jumpRange,
        public string $factionSymbol,
        #[DataCollectionOf(JumpGateSystemData::class)]
        public ?DataCollection $connectedSystems = null,
    ) {
        match (true) {
            !FactionSymbols::isValid($factionSymbol) => throw new InvalidArgumentException("Invalid faction symbol: {$factionSymbol}"),
            default => null,
        };
    }

    public static function fromResponse(array $response): static
    {
        return new self(
            jumpRange: $response['jumpRange'],
            factionSymbol: $response['factionSymbol'],
            connectedSystems: JumpGateSystemData::collectionFromResponse($response['connectedSystems']),
        );
    }
}
