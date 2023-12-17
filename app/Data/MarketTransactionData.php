<?php

declare(strict_types=1);

namespace App\Data;

use App\Enums\TradeSymbols;
use Spatie\LaravelData\Data;
use InvalidArgumentException;
use Illuminate\Support\Carbon;
use App\Enums\TransactionTypes;
use App\Traits\HasCollectionFromResponse;
use App\Interfaces\GeneratableFromResponse;

class MarketTransactionData extends Data implements GeneratableFromResponse
{
    use HasCollectionFromResponse;

    public function __construct(
        public string $waypointSymbol,
        public string $shipSymbol,
        public string $tradeSymbol,
        public string $type,
        public int $units,
        public int $pricePerUnit,
        public int $totalPrice,
        public Carbon $timestamp,
    ) {
        match (true) {
            !TradeSymbols::isValid($tradeSymbol) => throw new InvalidArgumentException("Invalid trade symbol: {$tradeSymbol}"),
            !TransactionTypes::isValid($type) => throw new InvalidArgumentException("Invalid transaction type: {$tradeSymbol}"),
            default => null,
        };
    }

    public static function fromResponse(array $response): static
    {
        return new static(
            waypointSymbol: $response['waypointSymbol'],
            shipSymbol: $response['shipSymbol'],
            tradeSymbol: $response['tradeSymbol'],
            type: $response['type'],
            units: $response['units'],
            pricePerUnit: $response['pricePerUnit'],
            totalPrice: $response['totalPrice'],
            timestamp: Carbon::parse($response['timestamp']),
        );
    }
}
