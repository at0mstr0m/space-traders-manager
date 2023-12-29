<?php

declare(strict_types=1);

namespace App\Data;

use App\Enums\FactionSymbols;
use App\Interfaces\GeneratableFromResponse;
use App\Traits\HasCollectionFromResponse;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;

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
            throw new \InvalidArgumentException("Invalid Faction symbol: {$symbol}");
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
