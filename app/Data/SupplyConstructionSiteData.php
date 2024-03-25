<?php

declare(strict_types=1);

namespace App\Data;

use App\Interfaces\UpdatesShip;
use App\Models\Ship;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Data;

class SupplyConstructionSiteData extends Data implements UpdatesShip
{
    public function __construct(
        #[MapInputName('construction')]
        public ConstructionSiteData $construction,
        #[MapInputName('cargo')]
        public ShipCargoData $cargo,
    ) {}

    public function updateShip(Ship $ship): Ship
    {
        return $this->cargo->updateShip($ship);
    }
}
