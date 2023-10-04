<?php

declare(strict_types=1);

namespace App\Data;

use App\Enums\TradeSymbols;
use Spatie\LaravelData\Data;
use InvalidArgumentException;
use Illuminate\Support\Carbon;
use App\Enums\TransactionTypes;
use Spatie\LaravelData\DataCollection;
use App\Traits\HasCollectionFromResponse;
use Spatie\LaravelData\Attributes\DataCollectionOf;

class ExtractionData extends Data
{
    use HasCollectionFromResponse;

    public function __construct(
        public string $shipSymbol,
        public string $tradeSymbol,
        public int $units,
        public Carbon $cooldownExpiresAt,
        public int $cargoCapacity,
        public int $cargoUnits,
        #[DataCollectionOf(CargoData::class)]
        public ?DataCollection $inventory = null,
    ) {
        if (!TradeSymbols::isValid($tradeSymbol)) {
            throw new InvalidArgumentException("Invalid trade symbol: {$tradeSymbol}");
        }
    }

    public static function fromResponse(array $response): static
    {
        return new self(
            shipSymbol: $response['extraction']['shipSymbol'],
            tradeSymbol: $response['extraction']['yield']['symbol'],
            units: $response['extraction']['yield']['units'],
            cooldownExpiresAt: Carbon::parse($response['cooldown']['expiration']),
            cargoCapacity: $response['cargo']['capacity'],
            cargoUnits: $response['cargo']['units'],
            inventory: CargoData::collectionFromResponse($response['cargo']['inventory']),
        );
    }
}
