<?php

declare(strict_types=1);

namespace App\Data;

use App\Enums\TradeSymbols;
use App\Interfaces\GeneratableFromResponse;
use App\Interfaces\UpdatesShip;
use App\Models\Cargo;
use App\Models\Ship;
use App\Traits\HasCollectionFromResponse;
use Illuminate\Support\Carbon;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;

class SiphonData extends Data implements GeneratableFromResponse, UpdatesShip
{
    use HasCollectionFromResponse;

    public function __construct(
        public string $shipSymbol,
        public string $tradeSymbol,
        public int $units,
        public Carbon $cooldownExpiresAt,
        public int $remainingSeconds,
        public int $cargoCapacity,
        public int $cargoUnits,
        #[DataCollectionOf(CargoData::class)]
        public ?DataCollection $inventory = null,
    ) {
        if (!TradeSymbols::isValid($tradeSymbol)) {
            throw new \InvalidArgumentException("Invalid trade symbol: {$tradeSymbol}");
        }
    }

    public static function fromResponse(array $response): static
    {
        return new static(
            shipSymbol: $response['siphon']['shipSymbol'],
            tradeSymbol: $response['siphon']['yield']['symbol'],
            units: $response['siphon']['yield']['units'],
            cooldownExpiresAt: Carbon::parse($response['cooldown']['expiration']),
            remainingSeconds: $response['cooldown']['remainingSeconds'],
            cargoCapacity: $response['cargo']['capacity'],
            cargoUnits: $response['cargo']['units'],
            inventory: CargoData::collectionFromResponse($response['cargo']['inventory']),
        );
    }

    public function updateShip(Ship $ship): Ship
    {
        $ship->cargos()->delete();
        $this->inventory
            ->each(
                fn (CargoData $cargoData) => Cargo::new($cargoData)->ship()->associate($ship)->save()
            );

        return $ship->fill([
            'cooldown' => $this->remainingSeconds,
            'cargo_capacity' => $this->cargoCapacity,
            'cargo_units' => $this->cargoUnits,
        ]);
    }
}
