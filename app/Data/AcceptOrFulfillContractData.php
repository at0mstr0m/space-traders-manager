<?php

declare(strict_types=1);

namespace App\Data;

use Spatie\LaravelData\Data;

class AcceptOrFulfillContractData extends Data
{
    public function __construct(
        public AgentData $agent,
        public ContractData $contract,
    ) {
    }

    public static function fromResponse(array $response): static
    {
        return new self(
            agent: AgentData::fromResponse($response['agent']),
            contract: ContractData::fromResponse($response['contract']),
        );
    }
}
