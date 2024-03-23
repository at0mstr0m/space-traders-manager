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

class ShipRefineData extends Data
{
    public function __construct(
        #[MapInputName('cargo')]
        public ShipCargoData $cargo,
        #[MapInputName('cooldown.expiration')]
        #[WithCast(CarbonCast::class)]
        public Carbon $cooldownExpiresAt,
        #[MapInputName('cooldown.remainingSeconds')]
        public int $remainingSeconds,
        #[MapInputName('produced.tradeSymbol')]
        #[WithCast(EnumCast::class)]
        public TradeSymbols $producedTradeSymbol,
        #[MapInputName('produced.units')]
        public int $producedUnits,
        #[MapInputName('consumed.tradeSymbol')]
        #[WithCast(EnumCast::class)]
        public TradeSymbols $consumedTradeSymbol,
        #[MapInputName('consumed.units')]
        public int $consumedUnits,
    ) {}
}
