<?php

namespace App\Data;

use App\Interfaces\GeneratableFromResponse;
use App\Interfaces\UpdatesShip;
use App\Models\Ship;
use Spatie\LaravelData\Data;

class SupplyConstructionSiteData extends Data implements GeneratableFromResponse, UpdatesShip
{
    public function __construct(
        public ConstructionSiteData $construction,
        public ShipCargoData $cargo,
    ) {}

    public static function fromResponse(array $response): static
    {
        return new static(
            construction: ConstructionSiteData::fromResponse($response['construction']),
            cargo: ShipCargoData::fromResponse($response['cargo']),
        );
    }

    public function updateShip(Ship $ship): Ship
    {
        return $this->cargo->updateShip($ship);
    }
}
