<?php

declare(strict_types=1);

namespace App\Data;

use App\Interfaces\GeneratableFromResponse;
use App\Traits\HasCollectionFromResponse;
use Illuminate\Support\Carbon;
use Spatie\LaravelData\Data;

class RepairScrapTransactionData extends Data implements GeneratableFromResponse
{
    use HasCollectionFromResponse;

    public function __construct(
        public string $waypointSymbol,
        public string $shipSymbol,
        public int $totalPrice,
        public Carbon $timestamp,
    ) {}

    public static function fromResponse(array $response): static
    {
        return new static(
            shipSymbol: $response['shipSymbol'],
            waypointSymbol: $response['waypointSymbol'],
            totalPrice: $response['totalPrice'],
            timestamp: Carbon::parse($response['timestamp']),
        );
    }
}
