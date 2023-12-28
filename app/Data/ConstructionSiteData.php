<?php

namespace App\Data;

use App\Interfaces\GeneratableFromResponse;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;

class ConstructionSiteData extends Data implements GeneratableFromResponse
{
    public function __construct(
        public string $waypointSymbol,
        #[DataCollectionOf(ConstructionMaterialData::class)]
        public ?DataCollection $constructionMaterial = null,
        public bool $isComplete,
    ) {}

    public static function fromResponse(array $response): static
    {
        return new static(
            waypointSymbol: $response['symbol'],
            constructionMaterial: ConstructionMaterialData::collectionFromResponse($response['materials']),
            isComplete: $response['isComplete'],
        );
    }
}
