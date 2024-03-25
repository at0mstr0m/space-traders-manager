<?php

declare(strict_types=1);

namespace App\Data;

use App\Actions\UpdateContractAction;
use App\Models\Contract;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Data;

class AcceptOrFulfillContractData extends Data
{
    public function __construct(
        #[MapInputName('agent')]
        public AgentData $agent,
        #[MapInputName('contract')]
        public ContractData $contract,
    ) {}

    public function updateContract(Contract $contract): Contract
    {
        return UpdateContractAction::run(
            $this->contract,
            $contract->agent
        );
    }
}
