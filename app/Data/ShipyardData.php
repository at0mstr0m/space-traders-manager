<?php

declare(strict_types=1);

namespace App\Data;

use App\Interfaces\GeneratableFromResponse;
use Illuminate\Support\Arr;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;

class ShipyardData extends Data implements GeneratableFromResponse
{
    public function __construct(
        public string $symbol,
        public int $modificationsFee,
        #[DataCollectionOf(ShipTypeData::class)]
        public ?DataCollection $shipTypes = null,
        #[DataCollectionOf(TransactionData::class)]
        public ?DataCollection $transactions = null,
        #[DataCollectionOf(ShipyardShipData::class)]
        public ?DataCollection $ships = null,
    ) {}

    public static function fromResponse(array $response): static
    {
        $ships = data_get($response, 'ships', []);
        $symbol = $response['symbol'];
        $ships = empty($ships) ? [] : Arr::map($ships, fn (array $ship) => [...$ship, 'waypointSymbol' => $symbol]);

        return new static(
            symbol: $response['symbol'],
            modificationsFee: $response['modificationsFee'],
            shipTypes: ShipTypeData::collectionFromResponse($response['shipTypes']),
            transactions: TransactionData::collectionFromResponse(data_get($response, 'transactions', [])),
            ships: ShipyardShipData::collectionFromResponse($ships),
        );
    }
}
