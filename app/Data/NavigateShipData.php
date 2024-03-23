<?php

declare(strict_types=1);

namespace App\Data;

use App\Interfaces\UpdatesShip;
use App\Models\Ship;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Data;

class NavigateShipData extends Data implements UpdatesShip
{
    public function __construct(
        #[MapInputName('fuel')]
        public FuelData $fuel,
        #[MapInputName('nav')]
        public NavigationData $nav,
    ) {}

    public function updateShip(Ship $ship): Ship
    {
        return $this->nav->updateShip(
            $this->fuel->updateShip($ship)
        );
    }
}
