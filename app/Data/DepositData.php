<?php

namespace App\Data;

use Spatie\LaravelData\Data;
use App\Enums\DepositSymbols;
use InvalidArgumentException;
use App\Traits\HasCollectionFromResponse;

class DepositData extends Data
{
    use HasCollectionFromResponse;

    private static string $responseTransformer = 'transformFromArrayResponse';

    public function __construct(
        public string $symbol
    ) {
        if (!DepositSymbols::isValid($symbol)) {
            throw new InvalidArgumentException("Invalid deposit symbol: {$symbol}");
        }
    }

    public static function transformFromArrayResponse(string $symbol): static
    {
        return new self(symbol: $symbol);
    }
}
