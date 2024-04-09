<?php

declare(strict_types=1);

namespace App\Data;

use App\Actions\UpdateContractAction;
use App\Interfaces\UpdatesShip;
use App\Models\Contract;
use App\Models\Ship;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Data;

class DeliverCargoToContractData extends Data implements UpdatesShip
{
    public function __construct(
        #[MapInputName('contract')]
        public ContractData $contract,
        #[MapInputName('cargo')]
        public ShipCargoData $cargo,
    ) {}

    public function updateContract(): static
    {
        UpdateContractAction::run(
            $this->contract,
            Contract::firstWhere('identification', $this->contract->identification)->agent
        );

        return $this;
    }

    public function updateShip(Ship $ship): Ship
    {
        $this->updateContract();

        return $this->cargo->updateShip($ship);
    }
}
