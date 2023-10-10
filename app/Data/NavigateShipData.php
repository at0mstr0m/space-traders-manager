<?php

declare(strict_types=1);

namespace App\Data;

use Spatie\LaravelData\Data;
use App\Interfaces\UpdatesShip;
use App\Interfaces\GeneratableFromResponse;
use App\Models\Ship;

class NavigateShipData extends Data implements GeneratableFromResponse, UpdatesShip
{
    public function __construct(
        public FuelData $fuel,
        public NavigationData $nav,
    ) {
    }

    public static function fromResponse(array $response): static
    {
        return new self(
            fuel: FuelData::fromResponse($response['fuel']),
            nav: NavigationData::fromResponse($response['nav']),
        );
    }

    public function updateShip(Ship $ship): Ship
    {
        return $this->nav->updateShip(
            $this->fuel->updateShip($ship)
        );
    }
}
