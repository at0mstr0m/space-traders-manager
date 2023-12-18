<?php

declare(strict_types=1);

namespace App\Data;

use App\Actions\UpdateContractAction;
use App\Interfaces\GeneratableFromResponse;
use App\Interfaces\UpdatesShip;
use App\Models\Agent;
use App\Models\Ship;
use Spatie\LaravelData\Data;

class DeliverCargoToContractData extends Data implements GeneratableFromResponse, UpdatesShip
{
    public function __construct(
        public ContractData $contract,
        public ShipCargoData $cargo,
    ) {}

    public static function fromResponse(array $response): static
    {
        return new static(
            contract: ContractData::fromResponse($response['contract']),
            cargo: ShipCargoData::fromResponse($response['cargo']),
        );
    }

    public function updateContract(): static
    {
        UpdateContractAction::run(
            $this->contract,
            Agent::firstWhere('identification', $this->contract->identification)
        );

        return $this;
    }

    public function updateShip(Ship $ship): Ship
    {
        $this->updateContract();

        return $this->cargo->updateShip($ship);
    }
}
