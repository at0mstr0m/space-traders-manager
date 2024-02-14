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
        public bool $isComplete,
        #[DataCollectionOf(ConstructionMaterialData::class)]
        public ?DataCollection $constructionMaterial = null,
    ) {}

    public static function fromResponse(array $response): static
    {
        return new static(
            waypointSymbol: $response['symbol'],
            isComplete: $response['isComplete'],
            constructionMaterial: ConstructionMaterialData::collectionFromResponse($response['materials']),
        );
    }
}
