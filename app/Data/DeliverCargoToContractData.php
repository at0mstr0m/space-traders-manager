<?php

declare(strict_types=1);

namespace App\Data;

use Spatie\LaravelData\Data;
use App\Interfaces\GeneratableFromResponse;

class DeliverCargoToContractData extends Data implements GeneratableFromResponse
{
    public function __construct(
        public ContractData $contract,
        public ShipCargoData $cargo,
    ) {
    }

    public static function fromResponse(array $response): static
    {
        return new self(
            contract: ContractData::fromResponse($response['contract']),
            cargo: ShipCargoData::fromResponse($response['cargo']),
        );
    }
}
