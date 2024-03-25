<?php

declare(strict_types=1);

namespace App\Data;

use App\Enums\FactionSymbols;
use App\Enums\WaypointTypes;
use Illuminate\Support\Arr;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Casts\EnumCast;
use Spatie\LaravelData\Data;

class ScannedWaypointData extends Data
{
    public function __construct(
        #[MapInputName('symbol')]
        public string $symbol,
        #[MapInputName('type')]
        #[WithCast(EnumCast::class)]
        public WaypointTypes $type,
        #[MapInputName('x')]
        public int $x,
        #[MapInputName('y')]
        public int $y,
        #[MapInputName('orbitals')]
        public array $orbitals,
        #[MapInputName('faction.symbol')]
        #[WithCast(EnumCast::class)]
        public FactionSymbols $faction,
        #[MapInputName('traits')]
        public array $traits,
        #[MapInputName('chart')]
        public ChartData $chart,
        #[MapInputName('orbits')]
        public ?string $orbits,
    ) {
        $this->orbitals = Arr::map($orbitals, fn (array $orbital) => $orbital['symbol']);
        $this->traits = Arr::map($traits, fn (array $orbital) => $orbital['symbol']);
    }
}
