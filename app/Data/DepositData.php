<?php

namespace App\Data;

use App\Enums\DepositSymbols;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Casts\EnumCast;
use Spatie\LaravelData\Data;

class DepositData extends Data
{
    public function __construct(
        #[MapInputName('symbol')]
        #[WithCast(EnumCast::class)]
        public DepositSymbols $symbol
    ) {}

    public static function fromResponseArray(array|string $symbol): static
    {
        return new static(
            symbol: DepositSymbols::fromName(is_array($symbol) ? $symbol['symbol'] : $symbol)
        );
    }
}
