<?php

declare(strict_types=1);

namespace App\Data;

use App\Models\Ship;
use App\Models\Cargo;
use App\Enums\TradeSymbols;
use Spatie\LaravelData\Data;
use InvalidArgumentException;
use Illuminate\Support\Carbon;
use App\Interfaces\UpdatesShip;
use Spatie\LaravelData\DataCollection;
use App\Traits\HasCollectionFromResponse;
use App\Interfaces\GeneratableFromResponse;
use Spatie\LaravelData\Attributes\DataCollectionOf;

class ExtractionData extends Data implements GeneratableFromResponse, UpdatesShip
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
            throw new InvalidArgumentException("Invalid trade symbol: {$tradeSymbol}");
        }
    }

    public static function fromResponse(array $response): static
    {
        return new static(
            shipSymbol: $response['extraction']['shipSymbol'],
            tradeSymbol: $response['extraction']['yield']['symbol'],
            units: $response['extraction']['yield']['units'],
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
