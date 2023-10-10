<?php

declare(strict_types=1);

namespace App\Data;

use Spatie\LaravelData\Data;
use App\Interfaces\GeneratableFromResponse;
use App\Interfaces\UpdatesShip;
use App\Models\Ship;
use Illuminate\Support\Carbon;

class FuelData extends Data implements GeneratableFromResponse, UpdatesShip
{
    public function __construct(
        public int $current,
        public int $capacity,
        public int $consumedAmount,
        public Carbon $consumedTimestamp,
    ) {
    }

    public static function fromResponse(array $response): static
    {
        return new static(
            current: $response['current'],
            capacity: $response['capacity'],
            consumedAmount: $response['consumed']['amount'],
            consumedTimestamp: Carbon::parse($response['consumed']['timestamp']),
        );
    }

    public function updateShip(Ship $ship): Ship
    {
        return $ship->fill([
            'fuel_current' => $this->current,
            'fuel_capacity' => $this->capacity,
            'fuel_consumed' => $this->consumedAmount,
        ]);
    }
}
