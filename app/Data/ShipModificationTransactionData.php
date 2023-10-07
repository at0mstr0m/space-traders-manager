<?php

declare(strict_types=1);

namespace App\Data;

use App\Enums\TradeSymbols;
use Spatie\LaravelData\Data;
use InvalidArgumentException;
use Illuminate\Support\Carbon;
use App\Interfaces\GeneratableFromResponse;

class ShipModificationTransactionData extends Data implements GeneratableFromResponse
{
    public function __construct(
        public string $waypointSymbol,
        public string $shipSymbol,
        public string $tradeSymbol,
        public int $totalPrice,
        public Carbon $timestamp,
    ) {
        match (true) {
            !TradeSymbols::isValid($tradeSymbol) => throw new InvalidArgumentException("Invalid trade symbol: {$tradeSymbol}"),
            default => null,
        };
    }

    public static function fromResponse(array $response): static
    {
        return new self(
            waypointSymbol: $response['waypointSymbol'],
            shipSymbol: $response['shipSymbol'],
            tradeSymbol: $response['tradeSymbol'],
            totalPrice: $response['totalPrice'],
            timestamp: Carbon::parse($response['timestamp']),
        );
    }
}
