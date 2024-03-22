<?php

declare(strict_types=1);

namespace App\Data;

use App\Enums\TradeSymbols;
use App\Traits\HasCollectionFromResponse;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Casts\EnumCast;
use Spatie\LaravelData\Data;

class CargoData extends Data
{
    use HasCollectionFromResponse;

    public function __construct(
        #[MapInputName('symbol')]
        #[WithCast(EnumCast::class)]
        public TradeSymbols $symbol,
        #[MapInputName('name')]
        public string $name,
        #[MapInputName('description')]
        public string $description,
        #[MapInputName('units')]
        public int $units,
    ) {}
}
