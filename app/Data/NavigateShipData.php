<?php

declare(strict_types=1);

namespace App\Data;

use App\Interfaces\UpdatesShip;
use App\Models\Ship;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Data;

class NavigateShipData extends Data implements UpdatesShip
{
    /**
     * @param Collection<int, ShipConditionEventData> $events
     */
    public function __construct(
        #[MapInputName('fuel')]
        public FuelData $fuel,
        #[MapInputName('nav')]
        public NavigationData $nav,
        #[MapInputName('events')]
        public Collection $events,
    ) {}

    public function updateShip(Ship $ship): Ship
    {
        $this->events->each(
            fn (ShipConditionEventData $eventData) => $eventData->save($ship)
        );

        return $this->nav->updateShip(
            $this->fuel->updateShip($ship)
        );
    }
}
