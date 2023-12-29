<?php

declare(strict_types=1);

namespace App\Data;

use App\Interfaces\GeneratableFromResponse;
use App\Interfaces\UpdatesShip;
use App\Models\Cargo;
use App\Models\Ship;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;

class ShipCargoData extends Data implements GeneratableFromResponse, UpdatesShip
{
    public function __construct(
        public int $capacity,
        public int $units,
        #[DataCollectionOf(CargoData::class)]
        public ?DataCollection $inventory = null,
    ) {}

    public static function fromResponse(array $response): static
    {
        return new static(
            capacity: $response['capacity'],
            units: $response['units'],
            inventory: CargoData::collectionFromResponse($response['inventory']),
        );
    }

    public function updateShip(Ship $ship): Ship
    {
        $ship->fill([
            'cargo_capacity' => $this->capacity,
            'cargo_units' => $this->units,
        ]);

        $ship->cargos()->delete();
        $this->inventory
            ->each(
                fn (CargoData $cargoData) => Cargo::new($cargoData)->ship()->associate($ship)->save()
            );

        return $ship;
    }
}
