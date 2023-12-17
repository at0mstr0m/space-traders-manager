<?php

declare(strict_types=1);

namespace App\Data;

use App\Enums\DepositSymbols;
use App\Traits\HasCollectionFromResponse;
use Spatie\LaravelData\Data;

class DepositData extends Data
{
    use HasCollectionFromResponse;

    private static string $responseTransformer = 'transformFromArrayResponse';

    public function __construct(
        public string $symbol
    ) {
        if (!DepositSymbols::isValid($symbol)) {
            throw new \InvalidArgumentException("Invalid deposit symbol: {$symbol}");
        }
    }

    public static function transformFromArrayResponse(array|string $symbol): static
    {
        return new static(symbol: is_array($symbol) ? $symbol['symbol'] : $symbol);
    }
}
