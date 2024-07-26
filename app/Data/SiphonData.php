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

class SiphonData extends Data implements UpdatesShip
{
    /**
     * @param Collection<int, CargoData> $inventory
     * @param Collection<int, ShipConditionEventData> $events
     */
    public function __construct(
        #[MapInputName('siphon.shipSymbol')]
        public string $shipSymbol,
        #[MapInputName('siphon.yield.symbol')]
        #[WithCast(EnumCast::class)]
        public TradeSymbols $tradeSymbol,
        #[MapInputName('siphon.yield.units')]
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
        #[MapInputName('events')]
        public Collection $events,
    ) {
        $this->events->each(
            fn (ShipConditionEventData $eventData) => $eventData
                ->setShipSymbol($this->shipSymbol)
                ->save()
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
