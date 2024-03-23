<?php

declare(strict_types=1);

namespace App\Data;

use App\Data\Casts\CarbonCast;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Data;

class ScanWaypointsData extends Data
{
    /**
     * @param Collection<int, ScannedWaypointData> $waypoints
     */
    public function __construct(
        #[MapInputName('cooldown.expiration')]
        #[WithCast(CarbonCast::class)]
        public Carbon $cooldownExpiresAt,
        #[MapInputName('cooldown.remainingSeconds')]
        public int $remainingSeconds,
        #[MapInputName('waypoints')]
        public Collection $waypoints,
    ) {}
}
