<?php

declare(strict_types=1);

namespace App\Data;

use App\Interfaces\GeneratableFromResponse;
use App\Traits\HasCollectionFromResponse;
use Spatie\LaravelData\Data;

class ScrapShipData extends Data implements GeneratableFromResponse
{
    use HasCollectionFromResponse;

    public function __construct(
        public AgentData $agent,
        public RepairScrapTransactionData $transaction,
    ) {}

    public static function fromResponse(array $response): static
    {
        return new static(
            agent: AgentData::fromResponse($response['agent']),
            transaction: RepairScrapTransactionData::fromResponse($response['transaction']),
        );
    }
}
