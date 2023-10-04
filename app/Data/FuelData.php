<?php

declare(strict_types=1);

namespace App\Data;

use Spatie\LaravelData\Data;
use App\Interfaces\GeneratableFromResponse;
use Illuminate\Support\Carbon;

class FuelData extends Data implements GeneratableFromResponse
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
}
