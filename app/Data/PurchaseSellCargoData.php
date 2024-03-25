<?php

declare(strict_types=1);

namespace App\Data;

use App\Interfaces\UpdatesShip;
use App\Models\Ship;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Data;

class PurchaseSellCargoData extends Data implements UpdatesShip
{
    public function __construct(
        #[MapInputName('agent')]
        public AgentData $agent,
        #[MapInputName('cargo')]
        public ShipCargoData $cargo,
        #[MapInputName('transaction')]
        public MarketTransactionData $transaction,
    ) {}

    public function updateShip(Ship $ship): Ship
    {
        $this->agent->updateAgent($ship->agent)->save();

        return $this->cargo->updateShip($ship);
    }
}
