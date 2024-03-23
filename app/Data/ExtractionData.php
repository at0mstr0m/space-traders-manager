<?php

declare(strict_types=1);

namespace App\Data;

use App\Data\Casts\CarbonCast;
use App\Enums\TradeSymbols;
use App\Interfaces\UpdatesShip;
use App\Models\Cargo;
use App\Models\Ship;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Casts\EnumCast;
use Spatie\LaravelData\Data;

class ExtractionData extends Data implements UpdatesShip
{
    /**
     * @param Collection<int, CargoData> $inventory
     */
    public function __construct(
        #[MapInputName('extraction.shipSymbol')]
        public string $shipSymbol,
        #[MapInputName('extraction.yield.symbol')]
        #[WithCast(EnumCast::class)]
        public TradeSymbols $tradeSymbol,
        #[MapInputName('extraction.yield.units')]
        public int $units,
        #[MapInputName('cooldown.expiration')]
        #[WithCast(CarbonCast::class)]
        public Carbon $cooldownExpiresAt,
        #[MapInputName('cooldown.remainingSeconds')]
        public int $remainingSeconds,
        #[MapInputName('cargo.capacity')]
        public int $cargoCapacity,
        #[MapInputName('cargo.units')]
        public int $cargoUnits,
        #[MapInputName('cargo.inventory')]
        public Collection $inventory,
    ) {}

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
