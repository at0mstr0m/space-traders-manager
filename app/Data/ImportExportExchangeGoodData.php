<?php

declare(strict_types=1);

namespace App\Data;

use App\Enums\TradeSymbols;
use App\Interfaces\GeneratableFromResponse;
use App\Traits\HasCollectionFromResponse;
use Spatie\LaravelData\Data;

class ImportExportExchangeGoodData extends Data implements GeneratableFromResponse
{
    use HasCollectionFromResponse;

    public function __construct(
        public string $symbol,
        public string $name,
        public string $description,
        public string $waypointSymbol,
    ) {
        match (true) {
            !TradeSymbols::isValid($symbol) => throw new \InvalidArgumentException("Invalid trade symbol: {$symbol}"),
            default => null,
        };
    }

    public static function fromResponse(array $response): static
    {
        return new static(
            symbol: $response['symbol'],
            name: $response['name'],
            description: $response['description'],
            waypointSymbol: $response['waypointSymbol'],
        );
    }
}
