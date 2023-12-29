<?php

declare(strict_types=1);

namespace App\Data;

use App\Interfaces\GeneratableFromResponse;
use App\Interfaces\UpdatesShip;
use App\Models\Ship;
use Spatie\LaravelData\Data;

class RefuelShipData extends Data implements GeneratableFromResponse, UpdatesShip
{
    public function __construct(
        public AgentData $agent,
        public FuelData $fuel,
        public MarketTransactionData $transaction,
    ) {}

    public static function fromResponse(array $response): static
    {
        return new static(
            agent: AgentData::fromResponse($response['agent']),
            fuel: FuelData::fromResponse($response['fuel']),
            transaction: MarketTransactionData::fromResponse($response['transaction']),
        );
    }

    public function updateShip(Ship $ship): Ship
    {
        return $this->fuel->updateShip($ship);
    }
}
