<?php

declare(strict_types=1);

namespace App\Data;

use Illuminate\Support\Collection;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Data;

class ShipyardData extends Data
{
    /**
     * @param Collection<int, ShipTypeData> $shipTypes
     * @param Collection<int, TransactionData> $transactions
     * @param Collection<int, ShipyardShipData> $ships
     */
    public function __construct(
        #[MapInputName('symbol')]
        public string $symbol,
        #[MapInputName('modificationsFee')]
        public int $modificationsFee,
        #[MapInputName('shipTypes')]
        public Collection $shipTypes,
        #[MapInputName('transactions')]
        public ?Collection $transactions = null,
        #[MapInputName('ships')]
        public ?Collection $ships = null,
    ) {
        $this->ships->transform(
            fn (ShipyardShipData $item) => $item->setWaypointSymbol($this->symbol)
        );
    }
}
