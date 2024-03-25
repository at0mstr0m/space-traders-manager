<?php

declare(strict_types=1);

namespace App\Data;

use App\Actions\UpdateShipAction;
use App\Interfaces\UpdatesShip;
use App\Models\Ship;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Data;

class RepairShipData extends Data implements UpdatesShip
{
    public function __construct(
        #[MapInputName('agent')]
        public AgentData $agent,
        #[MapInputName('ship')]
        public ShipData $ship,
        #[MapInputName('transaction')]
        public RepairScrapTransactionData $transaction,
    ) {}

    public function updateShip(Ship $ship): Ship
    {
        $this->agent->updateAgent($ship->agent)->save();

        return UpdateShipAction::run($this->ship, $ship->agent);
    }
}
