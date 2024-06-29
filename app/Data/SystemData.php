<?php

declare(strict_types=1);

namespace App\Data;

use App\Enums\SystemTypes;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Casts\EnumCast;
use Spatie\LaravelData\Data;

class SystemData extends Data
{
    /**
     * @param Collection<int, SystemWaypointData> $waypoints
     */
    public function __construct(
        #[MapInputName('symbol')]
        public string $symbol,
        #[MapInputName('sectorSymbol')]
        public string $sectorSymbol,
        #[MapInputName('type')]
        #[WithCast(EnumCast::class)]
        public SystemTypes $type,
        #[MapInputName('x')]
        public int $x,
        #[MapInputName('y')]
        public int $y,
        #[MapInputName('factions')]
        public array $factions,
        #[MapInputName('waypoints')]
        public Collection $waypoints,
    ) {
        /*
         * $factions looks like this:
         * [
         *     ["symbol" => "FACTION_SYMBOL"],
         *     ["symbol" => "ANOTHER_FACTION_SYMBOL"],
         *     ...
         * ]
         */
        $this->factions = Arr::map($factions, fn (array $faction) => $faction['symbol']);
    }
}
