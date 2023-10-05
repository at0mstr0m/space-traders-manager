<?php

declare(strict_types=1);

namespace App\Data;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;
use App\Interfaces\GeneratableFromResponse;
use Spatie\LaravelData\Attributes\DataCollectionOf;

class ShipCargoData extends Data implements GeneratableFromResponse
{
    public function __construct(
        public int $capacity,
        public int $units,
        #[DataCollectionOf(CargoData::class)]
        public ?DataCollection $inventory = null,
    ) {
    }

    public static function fromResponse(array $response): static
    {
        return new self(
            capacity: $response['capacity'],
            units: $response['units'],
            inventory: CargoData::collectionFromResponse($response['inventory']),
        );
    }
}
