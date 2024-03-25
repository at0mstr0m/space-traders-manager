<?php

declare(strict_types=1);

namespace App\Data;

use App\Enums\TradeSymbols;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Casts\EnumCast;
use Spatie\LaravelData\Data;

class ConstructionMaterialData extends Data
{
    public function __construct(
        #[MapInputName('tradeSymbol')]
        #[WithCast(EnumCast::class)]
        public TradeSymbols $tradeSymbol,
        #[MapInputName('required')]
        public int $unitsRequired,
        #[MapInputName('fulfilled')]
        public int $unitsFulfilled,
    ) {}
}
