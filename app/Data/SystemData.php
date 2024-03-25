<?php

declare(strict_types=1);

namespace App\Data;

use App\Enums\SystemTypes;
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
    ) {}
}
