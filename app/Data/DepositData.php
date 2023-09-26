<?php

namespace App\Data;

use Spatie\LaravelData\Data;
use App\Enums\DepositSymbols;
use InvalidArgumentException;

class DepositData extends Data
{
    public function __construct(
        public string $symbol
    ) {
        if (!DepositSymbols::isValid($symbol)) {
            throw new InvalidArgumentException("Invalid deposit symbol: {$symbol}");
        }
    }
}
