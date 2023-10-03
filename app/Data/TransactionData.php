<?php

namespace App\Data;

use Spatie\LaravelData\Data;
use Illuminate\Support\Carbon;
use App\Traits\HasCollectionFromResponse;
use App\Interfaces\GeneratableFromResponse;

class TransactionData extends Data implements GeneratableFromResponse
{
    use HasCollectionFromResponse;

    public function __construct(
        public string $shipSymbol,
        public string $waypointSymbol,
        public string $agentSymbol,
        public int $price,
        public Carbon $timestamp,
    ) {
    }

    public static function fromResponse(array $response): static
    {
        return new self(
            shipSymbol: $response['shipSymbol'],
            waypointSymbol: $response['waypointSymbol'],
            agentSymbol: $response['agentSymbol'],
            price: $response['price'],
            timestamp: Carbon::parse($response['timestamp']),
        );
    }
}
