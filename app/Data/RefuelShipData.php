<?php

declare(strict_types=1);

namespace App\Data;

use App\Interfaces\UpdatesShip;
use App\Models\Ship;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Data;

class RefuelShipData extends Data implements UpdatesShip
{
    public function __construct(
        #[MapInputName('agent')]
        public AgentData $agent,
        #[MapInputName('fuel')]
        public FuelData $fuel,
        #[MapInputName('transaction')]
        public MarketTransactionData $transaction,
    ) {}

    public function updateShip(Ship $ship): Ship
    {
        $this->agent->updateAgent($ship->agent)->save();

        return $this->fuel->updateShip($ship);
    }
}
