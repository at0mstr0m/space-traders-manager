<?php

declare(strict_types=1);

namespace App\Data;

use Spatie\LaravelData\Data;
use App\Interfaces\GeneratableFromResponse;

class PurchaseSellCargoData extends Data implements GeneratableFromResponse
{
    public function __construct(
        public AgentData $agent,
        public ShipCargoData $cargo,
        public MarketTransactionData $transaction,
    ) {
    }

    public static function fromResponse(array $response): static
    {
        return new self(
            agent: AgentData::fromResponse($response['agent']),
            cargo: ShipCargoData::fromResponse($response['cargo']),
            transaction: MarketTransactionData::fromResponse($response['transaction']),
        );
    }
}
