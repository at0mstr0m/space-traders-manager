<?php

declare(strict_types=1);

namespace App\Data;

use App\Data\Casts\CarbonCast;
use App\Enums\TradeSymbols;
use Illuminate\Support\Carbon;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Casts\EnumCast;
use Spatie\LaravelData\Data;

class ShipModificationTransactionData extends Data
{
    public function __construct(
        #[MapInputName('waypointSymbol')]
        public string $waypointSymbol,
        #[MapInputName('shipSymbol')]
        public string $shipSymbol,
        #[MapInputName('tradeSymbol')]
        #[WithCast(EnumCast::class)]
        public TradeSymbols $tradeSymbol,
        #[MapInputName('totalPrice')]
        public int $totalPrice,
        #[MapInputName('timestamp')]
        #[WithCast(CarbonCast::class)]
        public Carbon $timestamp,
    ) {}
}
