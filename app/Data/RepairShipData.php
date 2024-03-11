<?php

declare(strict_types=1);

namespace App\Data;

use App\Actions\UpdateShipAction;
use App\Interfaces\GeneratableFromResponse;
use App\Interfaces\UpdatesShip;
use App\Models\Ship;
use App\Traits\HasCollectionFromResponse;
use Spatie\LaravelData\Data;

class RepairShipData extends Data implements GeneratableFromResponse, UpdatesShip
{
    use HasCollectionFromResponse;

    public function __construct(
        public AgentData $agent,
        public ShipData $ship,
        public RepairScrapTransactionData $transaction,
    ) {}


    public static function fromResponse(array $response): static
    {
        return new static(
            agent: AgentData::fromResponse($response['agent']),
            ship: ShipData::fromResponse($response['ship']),
            transaction: RepairScrapTransactionData::fromResponse($response['transaction']),
        );
    }

    public function updateShip(Ship $ship): Ship
    {
        $this->agent->updateAgent($ship->agent)->save();

        return UpdateShipAction::run($this->ship, $ship->agent);
    }
}
