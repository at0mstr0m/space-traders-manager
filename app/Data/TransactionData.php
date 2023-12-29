<?php

declare(strict_types=1);

namespace App\Data;

use App\Interfaces\GeneratableFromResponse;
use App\Traits\HasCollectionFromResponse;
use Illuminate\Support\Carbon;
use Spatie\LaravelData\Data;

class TransactionData extends Data implements GeneratableFromResponse
{
    use HasCollectionFromResponse;

    public function __construct(
        public string $shipSymbol,
        public string $waypointSymbol,
        public string $agentSymbol,
        public int $price,
        public Carbon $timestamp,
    ) {}

    public static function fromResponse(array $response): static
    {
        return new static(
            shipSymbol: $response['shipSymbol'],
            waypointSymbol: $response['waypointSymbol'],
            agentSymbol: $response['agentSymbol'],
            price: $response['price'],
            timestamp: Carbon::parse($response['timestamp']),
        );
    }
}
