<?php

namespace App\Data;

use App\Enums\TradeSymbols;
use App\Interfaces\GeneratableFromResponse;
use App\Traits\HasCollectionFromResponse;
use Spatie\LaravelData\Data;

class ConstructionMaterialData extends Data implements GeneratableFromResponse
{
    use HasCollectionFromResponse;

    public function __construct(
        public string $tradeSymbol,
        public int $unitsRequired,
        public int $unitsFulfilled,
    ) {
        if (!TradeSymbols::isValid($tradeSymbol)) {
            throw new \InvalidArgumentException("Invalid trade symbol: {$tradeSymbol}");
        }
    }

    public static function fromResponse(array $response): static
    {
        return new static(
            tradeSymbol: $response['tradeSymbol'],
            unitsRequired: $response['required'],
            unitsFulfilled: $response['fulfilled'],
        );
    }
}
