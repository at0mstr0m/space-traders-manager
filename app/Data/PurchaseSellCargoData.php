<?php

declare(strict_types=1);

namespace App\Data;

use Spatie\LaravelData\Data;
use App\Interfaces\UpdatesShip;
use App\Interfaces\GeneratableFromResponse;
use App\Models\Ship;

class PurchaseSellCargoData extends Data implements GeneratableFromResponse, UpdatesShip
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

    public function updateShip(Ship $ship): Ship
    {
        return $this->cargo->updateShip($ship);
    }
}
