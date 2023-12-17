<?php

declare(strict_types=1);

namespace App\Data;

use Spatie\LaravelData\Data;
use App\Enums\FactionSymbols;
use InvalidArgumentException;
use Spatie\LaravelData\DataCollection;
use App\Traits\HasCollectionFromResponse;
use App\Interfaces\GeneratableFromResponse;
use Spatie\LaravelData\Attributes\DataCollectionOf;

class FactionData extends Data implements GeneratableFromResponse
{
    use HasCollectionFromResponse;

    public function __construct(
        public string $symbol,
        public string $name,
        public string $description,
        public string $headquarters,
        public bool $isRecruiting,
        #[DataCollectionOf(FactionTraitData::class)]
        public ?DataCollection $traits = null,
    ) {
        if (!FactionSymbols::isValid($symbol)) {
            throw new InvalidArgumentException("Invalid Faction symbol: {$symbol}");
        }
    }

    public static function fromResponse(array $response): static
    {
        return new static(
            symbol: $response['symbol'],
            name: $response['name'],
            description: $response['description'],
            headquarters: $response['headquarters'],
            isRecruiting: $response['isRecruiting'],
            traits: FactionTraitData::collectionFromResponse($response['traits']),
        );
    }
}
