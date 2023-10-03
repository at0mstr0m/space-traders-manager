<?php

declare(strict_types=1);

namespace App\Data;

use App\Enums\TradeSymbols;
use Spatie\LaravelData\Data;
use InvalidArgumentException;
use App\Traits\HasCollectionFromResponse;
use App\Interfaces\GeneratableFromResponse;

class DeliveryData extends Data implements GeneratableFromResponse
{
    use HasCollectionFromResponse;

    public function __construct(
        public string $tradeSymbol,
        public string $destinationSymbol,
        public int $unitsRequired,
        public int $unitsFulfilled,
    ) {
        if (!TradeSymbols::isValid($tradeSymbol)) {
            throw new InvalidArgumentException("Invalid trade symbol: {$tradeSymbol}");
        }
    }

    public static function fromResponse(array $response): static
    {
        return new static(...$response);
    }
}
