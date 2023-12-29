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
        #[DataCollectionOf(MountData::class)]
        public ?DataCollection $mounts = null,
        public ShipCargoData $cargo,
        public ShipModificationTransactionData $transaction,
    ) {}

    public static function fromResponse(array $response): static
    {
        return new static(
            agent: AgentData::fromResponse($response['agent']),
            mounts: MountData::collectionFromResponse($response['mounts']),
            cargo: ShipCargoData::fromResponse($response['cargo']),
            transaction: ShipModificationTransactionData::fromResponse($response['transaction']),
        );
    }
}
