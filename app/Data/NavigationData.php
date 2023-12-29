<?php

declare(strict_types=1);

namespace App\Data;

use App\Enums\FlightModes;
use App\Enums\ShipNavStatus;
use App\Enums\WaypointTypes;
use App\Interfaces\GeneratableFromResponse;
use App\Interfaces\UpdatesShip;
use App\Models\Ship;
use Illuminate\Support\Carbon;
use Spatie\LaravelData\Data;

class NavigationData extends Data implements GeneratableFromResponse, UpdatesShip
{
    public function __construct(
        public string $systemSymbol,
        public string $waypointSymbol,
        public string $status,
        public string $flightMode,
        public string $originSymbol,
        public string $originType,
        public string $destinationSymbol,
        public string $destinationType,
        public Carbon $departureTime,
        public Carbon $arrival,
    ) {
        match (true) {
            !ShipNavStatus::isValid($status) => throw new \InvalidArgumentException("Invalid ship nav status: {$status}"),
            !FlightModes::isValid($flightMode) => throw new \InvalidArgumentException("Invalid flight mode: {$flightMode}"),
            !WaypointTypes::isValid($originType) => throw new \InvalidArgumentException("Invalid origin type: {$flightMode}"),
            !WaypointTypes::isValid($destinationType) => throw new \InvalidArgumentException("Invalid destination type: {$flightMode}"),
            default => null,
        };
    }

    public static function fromResponse(array $response): static
    {
        return new static(
            systemSymbol: $response['systemSymbol'],
            waypointSymbol: $response['waypointSymbol'],
            status: $response['status'],
            flightMode: $response['flightMode'],
            originSymbol: $response['route']['origin']['symbol'],
            originType: $response['route']['origin']['type'],
            destinationSymbol: $response['route']['destination']['symbol'],
            destinationType: $response['route']['destination']['type'],
            departureTime: Carbon::parse($response['route']['departureTime']),
            arrival: Carbon::parse($response['route']['arrival']),
        );
    }

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
