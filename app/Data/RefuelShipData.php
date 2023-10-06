<?php

declare(strict_types=1);

namespace App\Data;

use Spatie\LaravelData\Data;
use App\Interfaces\GeneratableFromResponse;

class RefuelShipData extends Data implements GeneratableFromResponse
{
    public function __construct(
        public AgentData $agent,
        public FuelData $fuel,
        public MarketTransactionData $transaction,
    ) {
    }

    public static function fromResponse(array $response): static
    {
        return new self(
            agent: AgentData::fromResponse($response['agent']),
            fuel: FuelData::fromResponse($response['fuel']),
            transaction: MarketTransactionData::fromResponse($response['transaction']),
        );
    }
}
