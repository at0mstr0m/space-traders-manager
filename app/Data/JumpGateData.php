<?php

declare(strict_types=1);

namespace App\Data;

use App\Enums\FactionSymbols;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Casts\EnumCast;
use Spatie\LaravelData\Data;

class JumpGateData extends Data
{
    /**
     * @param Collection<int, JumpGateSystemData> $connectedSystems
     */
    public function __construct(
        #[MapInputName('jumpRange')]
        public int $jumpRange,
        #[MapInputName('factionSymbol')]
        #[WithCast(EnumCast::class)]
        public FactionSymbols $factionSymbol,
        #[MapInputName('connectedSystems')]
        public ?Collection $connectedSystems = null,
    ) {}
}
