<?php

declare(strict_types=1);

namespace App\Data;

use Spatie\LaravelData\Data;
use Illuminate\Support\Carbon;
use App\Traits\HasCollectionFromResponse;
use App\Interfaces\GeneratableFromResponse;

class ShipRefineData extends Data implements GeneratableFromResponse
{
    use HasCollectionFromResponse;

    public function __construct(
        public string $shipSymbol,
        public Carbon $expiration,
        public string $producedTradeSymbol,
        public string $producedUnits,
        public string $consumedTradeSymbol,
        public string $consumedUnits,
        public ShipCargoData $cargo,
    ) {
    }

    public static function fromResponse(array $response): static
    {
        return new static(
            shipSymbol: $response['cooldown']['shipSymbol'],
            expiration: Carbon::parse($response['cooldown']['expiration']),
            producedTradeSymbol: $response['produced']['tradeSymbol'],
            producedUnits: $response['produced']['units'],
            consumedTradeSymbol: $response['consumed']['tradeSymbol'],
            consumedUnits: $response['consumed']['units'],
            cargo: ShipCargoData::fromResponse($response['cargo']),
        );
    }
}
