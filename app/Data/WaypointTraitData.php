<?php

declare(strict_types=1);

namespace App\Data;

use App\Enums\WaypointTraitSymbols;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Casts\EnumCast;
use Spatie\LaravelData\Data;

class WaypointTraitData extends Data
{
    public function __construct(
        #[MapInputName('symbol')]
        #[WithCast(EnumCast::class)]
        public WaypointTraitSymbols $symbol,
        #[MapInputName('name')]
        public string $name,
        #[MapInputName('description')]
        public string $description,
    ) {}
}
