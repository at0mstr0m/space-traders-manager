<?php

declare(strict_types=1);

namespace App\Data;

use App\Interfaces\GeneratableFromResponse;
use Spatie\LaravelData\Data;

class PurchaseShipData extends Data implements GeneratableFromResponse
{
    public function __construct(
        public AgentData $agent,
        public ShipData $ship,
        public TransactionData $transaction,
    ) {}

    public static function fromResponse(array $response): static {
        return new static(
            agent: AgentData::fromResponse($response['agent']),
            ship: ShipData::fromResponse($response['ship']),
            transaction: TransactionData::fromResponse($response['transaction']),
        );
    }
}
