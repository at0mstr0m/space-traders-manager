<?php

declare(strict_types=1);

namespace App\Data;

use App\Enums\ActivityLevels;
use App\Enums\SupplyLevels;
use App\Enums\TradeGoodTypes;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Casts\EnumCast;
use Spatie\LaravelData\Data;

class TradeGoodsData extends Data
{
    public function __construct(
        #[MapInputName('symbol')]
        public string $symbol,
        #[MapInputName('type')]
        #[WithCast(EnumCast::class)]
        public TradeGoodTypes $tradeGoodType,
        #[MapInputName('tradeVolume')]
        public int $tradeVolume,
        #[MapInputName('supply')]
        #[WithCast(EnumCast::class)]
        public SupplyLevels $supplyLevel,
        #[MapInputName('purchasePrice')]
        public int $purchasePrice,
        #[MapInputName('sellPrice')]
        public int $sellPrice,
        #[MapInputName('activity')]
        #[WithCast(EnumCast::class)]
        public ?ActivityLevels $activityLevel = null,  // exchanges so not have an activity level
    ) {}
}
