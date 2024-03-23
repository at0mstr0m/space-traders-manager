<?php

declare(strict_types=1);

namespace App\Data;

use App\Interfaces\UpdatesShip;
use App\Models\Cargo;
use App\Models\Ship;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Data;

class ShipCargoData extends Data implements UpdatesShip
{
    /**
     * @param Collection<int, CargoData> $inventory
     */
    public function __construct(
        #[MapInputName('capacity')]
        public int $capacity,
        #[MapInputName('units')]
        public int $units,
        #[MapInputName('inventory')]
        public Collection $inventory,
    ) {}

    public function updateShip(Ship $ship): Ship
    {
        $ship->fill([
            'cargo_capacity' => $this->capacity,
            'cargo_units' => $this->units,
        ]);

        $ship->cargos()->delete();

        $this->inventory->each(
            fn (CargoData $cargoData) => Cargo::new($cargoData)
                ->ship()
                ->associate($ship)
                ->save()
        );

        return $ship;
    }
}
