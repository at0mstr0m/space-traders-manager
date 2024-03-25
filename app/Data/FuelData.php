<?php

declare(strict_types=1);

namespace App\Data;

use App\Data\Casts\CarbonCast;
use App\Interfaces\UpdatesShip;
use App\Models\Ship;
use Illuminate\Support\Carbon;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Data;

class FuelData extends Data implements UpdatesShip
{
    public function __construct(
        #[MapInputName('current')]
        public int $current,
        #[MapInputName('capacity')]
        public int $capacity,
        #[MapInputName('consumed.amount')]
        public int $consumedAmount,
        #[MapInputName('consumed.timestamp')]
        #[WithCast(CarbonCast::class)]
        public Carbon $consumedTimestamp,
    ) {}

    public function updateShip(Ship $ship): Ship
    {
        return $ship->fill([
            'fuel_current' => $this->current,
            'fuel_capacity' => $this->capacity,
            'fuel_consumed' => $this->consumedAmount,
        ]);
    }
}
