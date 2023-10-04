<?php

declare(strict_types=1);

namespace App\Data;

use App\Enums\FlightModes;
use App\Enums\ShipNavStatus;
use App\Enums\WaypointTypes;
use Spatie\LaravelData\Data;
use InvalidArgumentException;
use Illuminate\Support\Carbon;
use App\Interfaces\GeneratableFromResponse;

class NavigationData extends Data implements GeneratableFromResponse
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
            !ShipNavStatus::isValid($status) => throw new InvalidArgumentException("Invalid ship nav status: {$status}"),
            !FlightModes::isValid($flightMode) => throw new InvalidArgumentException("Invalid flight mode: {$flightMode}"),
            !WaypointTypes::isValid($originType) => throw new InvalidArgumentException("Invalid origin type: {$flightMode}"),
            !WaypointTypes::isValid($destinationType) => throw new InvalidArgumentException("Invalid destination type: {$flightMode}"),
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
}
