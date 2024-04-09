<?php

declare(strict_types=1);

namespace App\Data;

use App\Actions\UpdateContractAction;
use App\Interfaces\UpdatesAgent;
use App\Models\Agent;
use App\Models\Contract;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Data;

class AcceptOrFulfillContractData extends Data implements UpdatesAgent
{
    public function __construct(
        #[MapInputName('agent')]
        public AgentData $agent,
        #[MapInputName('contract')]
        public ContractData $contract,
    ) {}

    public function updateAgent(Agent $agent): Agent
    {
        return $this->agent->updateAgent($agent);
    }

    public function updateContract(Contract $contract): Contract
    {
        return UpdateContractAction::run(
            $this->contract,
            $contract->agent
        );
    }
}
