<?php

declare(strict_types=1);

namespace App\Data;

use App\Interfaces\GeneratableFromResponse;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;

class ShipyardData extends Data implements GeneratableFromResponse
{
    public function __construct(
        public string $symbol,
        #[DataCollectionOf(ShipTypeData::class)]
        public ?DataCollection $shipTypes = null,
        #[DataCollectionOf(TransactionData::class)]
        public ?DataCollection $transactions = null,
        #[DataCollectionOf(ShipyardShipData::class)]
        public ?DataCollection $ships = null,
        public int $modificationsFee,
    ) {}

    public static function fromResponse(array $response): static
    {
        return new static(
            symbol: $response['symbol'],
            shipTypes: ShipTypeData::collectionFromResponse($response['shipTypes']),
            transactions: TransactionData::collectionFromResponse(data_get($response, 'transactions', [])),
            ships: ShipyardShipData::collectionFromResponse(data_get($response, 'ships', [])),
            modificationsFee: $response['modificationsFee'],
        );
    }
}
