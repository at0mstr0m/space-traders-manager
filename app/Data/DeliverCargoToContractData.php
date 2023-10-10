<?php

declare(strict_types=1);

namespace App\Data;

use Spatie\LaravelData\Data;
use App\Interfaces\UpdatesShip;
use App\Interfaces\GeneratableFromResponse;
use App\Models\Ship;

class DeliverCargoToContractData extends Data implements GeneratableFromResponse, UpdatesShip
{
    public function __construct(
        public ContractData $contract,
        public ShipCargoData $cargo,
    ) {
    }


    public static function fromResponse(array $response): static
    {
        return new self(
            contract: ContractData::fromResponse($response['contract']),
            cargo: ShipCargoData::fromResponse($response['cargo']),
        );
    }

    public function updateShip(Ship $ship): Ship
    {
        return $this->cargo->updateShip($ship);
    }
}
