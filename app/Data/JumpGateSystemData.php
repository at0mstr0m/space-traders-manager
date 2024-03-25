<?php

declare(strict_types=1);

namespace App\Data;

use App\Enums\FactionSymbols;
use App\Enums\SystemTypes;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Casts\EnumCast;
use Spatie\LaravelData\Data;

class JumpGateSystemData extends Data
{
    public function __construct(
        #[MapInputName('symbol')]
        public string $symbol,
        #[MapInputName('type')]
        #[WithCast(EnumCast::class)]
        public SystemTypes $type,
        #[MapInputName('factionSymbol')]
        #[WithCast(EnumCast::class)]
        public FactionSymbols $factionSymbol,
        #[MapInputName('x')]
        public int $x,
        #[MapInputName('y')]
        public int $y,
        #[MapInputName('distance')]
        public int $distance,
    ) {}
}
