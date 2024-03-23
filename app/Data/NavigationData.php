<?php

declare(strict_types=1);

namespace App\Data;

use App\Data\Casts\CarbonCast;
use App\Enums\FlightModes;
use App\Enums\ShipNavStatus;
use App\Enums\WaypointTypes;
use App\Interfaces\UpdatesShip;
use App\Models\Ship;
use Illuminate\Support\Carbon;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Casts\EnumCast;
use Spatie\LaravelData\Data;

class NavigationData extends Data implements UpdatesShip
{
    public function __construct(
        #[MapInputName('waypointSymbol')]
        public string $waypointSymbol,
        #[MapInputName('status')]
        #[WithCast(EnumCast::class)]
        public ShipNavStatus $status,
        #[MapInputName('flightMode')]
        #[WithCast(EnumCast::class)]
        public FlightModes $flightMode,
        #[MapInputName('route.origin.symbol')]
        public string $originSymbol,
        #[MapInputName('route.origin.type')]
        #[WithCast(EnumCast::class)]
        public WaypointTypes $originType,
        #[MapInputName('route.destination.symbol')]
        public string $destinationSymbol,
        #[MapInputName('route.destination.type')]
        #[WithCast(EnumCast::class)]
        public WaypointTypes $destinationType,
        #[MapInputName('route.departureTime')]
        #[WithCast(CarbonCast::class)]
        public Carbon $departureTime,
        #[MapInputName('route.arrival')]
        #[WithCast(CarbonCast::class)]
        public Carbon $arrival,
    ) {}

    public function updateShip(Ship $ship): Ship
    {
        $now = Carbon::now();
        $cooldown = $now->isBefore($this->arrival)
            ? $now->diffInSeconds($this->arrival)
            : 0;

        return $ship->fill([
            'waypoint_symbol' => $this->waypointSymbol,
            'status' => $this->status,
            'flight_mode' => $this->flightMode,
            'cooldown' => $cooldown,
        ]);
    }
}
