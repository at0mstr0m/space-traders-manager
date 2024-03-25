<?php

declare(strict_types=1);

namespace App\Data;

use App\Enums\EngineSymbols;
use App\Enums\FactionSymbols;
use App\Enums\FrameSymbols;
use App\Enums\ReactorSymbols;
use App\Enums\ShipRoles;
use Illuminate\Support\Arr;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Casts\EnumCast;
use Spatie\LaravelData\Data;

class ScannedShipData extends Data
{
    public function __construct(
        #[MapInputName('symbol')]
        public string $symbol,
        #[MapInputName('registration.name')]
        public string $name,
        #[MapInputName('registration.factionSymbol')]
        #[WithCast(EnumCast::class)]
        public FactionSymbols $factionSymbol,
        #[MapInputName('registration.role')]
        #[WithCast(EnumCast::class)]
        public ShipRoles $role,
        #[MapInputName('nav')]
        public NavigationData $nav,
        #[MapInputName('frame.symbol')]
        #[WithCast(EnumCast::class)]
        public FrameSymbols $frameSymbol,
        #[MapInputName('reactor.symbol')]
        #[WithCast(EnumCast::class)]
        public ReactorSymbols $reactorSymbol,
        #[MapInputName('engine.symbol')]
        #[WithCast(EnumCast::class)]
        public EngineSymbols $engineSymbol,
        #[MapInputName('mounts')]
        public array $mounts,
    ) {
        $this->mounts = Arr::map($mounts, fn (array $mount) => $mount['symbol']);
    }
}
