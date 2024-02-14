<?php

namespace App\Data;

use App\Interfaces\GeneratableFromResponse;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;

class InstallRemoveMountData extends Data implements GeneratableFromResponse
{
    public function __construct(
        public AgentData $agent,
        public ShipCargoData $cargo,
        public ShipModificationTransactionData $transaction,
        #[DataCollectionOf(MountData::class)]
        public ?DataCollection $mounts = null,
    ) {}

    public static function fromResponse(array $response): static
    {
        return new static(
            agent: AgentData::fromResponse($response['agent']),
            cargo: ShipCargoData::fromResponse($response['cargo']),
            transaction: ShipModificationTransactionData::fromResponse($response['transaction']),
            mounts: MountData::collectionFromResponse($response['mounts']),
        );
    }
}
