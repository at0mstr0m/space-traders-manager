<?php

declare(strict_types=1);

namespace App\Data;

use App\Enums\FactionSymbols;
use App\Enums\WaypointTypes;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Casts\EnumCast;
use Spatie\LaravelData\Data;

class WaypointData extends Data
{
    /**
     * @param Collection<int, WaypointOrbitalData> $orbitals
     * @param Collection<int, WaypointTraitData> $traits
     * @param Collection<int, WaypointModifierData> $modifiers
     */
    public function __construct(
        #[MapInputName('symbol')]
        public string $symbol,
        #[MapInputName('systemSymbol')]
        public string $systemSymbol,
        #[MapInputName('type')]
        #[WithCast(EnumCast::class)]
        public WaypointTypes $type,
        #[MapInputName('x')]
        public int $x,
        #[MapInputName('y')]
        public int $y,
        #[MapInputName('faction.symbol')]
        #[WithCast(EnumCast::class)]
        public ?FactionSymbols $faction,
        #[MapInputName('orbitals')]
        public Collection $orbitals,
        #[MapInputName('traits')]
        public Collection $traits,
        #[MapInputName('modifiers')]
        public Collection $modifiers,
        #[MapInputName('isUnderConstruction')]
        public bool $isUnderConstruction,
        #[MapInputName('orbits')]
        public ?string $orbits = null,
    ) {}
}
