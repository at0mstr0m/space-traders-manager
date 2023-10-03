<?php

declare(strict_types=1);

namespace App\Data;

use App\Enums\ShipTypes;
use Illuminate\Support\Arr;
use Spatie\LaravelData\Data;
use InvalidArgumentException;
use Spatie\LaravelData\DataCollection;
use App\Traits\HasCollectionFromResponse;
use App\Interfaces\GeneratableFromResponse;
use Spatie\LaravelData\Attributes\DataCollectionOf;

class ShipyardShipData extends Data implements GeneratableFromResponse
{
    use HasCollectionFromResponse;

    public function __construct(
        public string $type,
        public string $name,
        public string $description,
        public int $purchasePrice,
        public FrameData $frame,
        public ReactorData $reactor,
        public EngineData $engine,
        #[DataCollectionOf(ModuleData::class)]
        public ?DataCollection $modules = null,
        #[DataCollectionOf(MountData::class)]
        public ?DataCollection $mounts = null,
        public int $crewCapacity,
        public int $crewRequired,
    ) {
        if (!ShipTypes::isValid($type)) {
            throw new InvalidArgumentException("Invalid ship type: {$type}");
        }
    }

    public static function fromResponse(array $response): static
    {
        return new self(
            type: $response['type'],
            name: $response['name'],
            description: $response['description'],
            purchasePrice: $response['purchasePrice'],
            frame: FrameData::fromResponse($response['frame']),
            reactor: ReactorData::fromResponse($response['reactor']),
            engine: EngineData::fromResponse($response['engine']),
            modules: ModuleData::collectionFromResponse($response['modules']),
            mounts: MountData::collectionFromResponse($response['mounts']),
            crewCapacity: $response['crew']['capacity'],
            crewRequired: $response['crew']['required'],
        );
    }
}
