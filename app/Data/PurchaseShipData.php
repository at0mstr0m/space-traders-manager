<?php

declare(strict_types=1);

namespace App\Data;

use App\Interfaces\UpdatesAgent;
use App\Models\Agent;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Data;

class PurchaseShipData extends Data implements UpdatesAgent
{
    public function __construct(
        #[MapInputName('agent')]
        public AgentData $agent,
        #[MapInputName('ship')]
        public ShipData $ship,
        #[MapInputName('transaction')]
        public TransactionData $transaction,
    ) {
        $this->updateAgent(Agent::findBySymbol($this->agent->symbol))
            ->save();
    }

    public function updateAgent(Agent $agent): Agent
    {
        return $this->agent->updateAgent($agent);
    }
}
