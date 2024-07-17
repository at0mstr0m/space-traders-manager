<?php

declare(strict_types=1);

namespace App\Data;

use App\Actions\UpdateWaypointAction;
use App\Models\Waypoint;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Data;

class CreateChartData extends Data
{
    public function __construct(
        #[MapInputName('chart')]
        public ChartData $chart,
        #[MapInputName('waypoint')]
        public WaypointData $waypoint,
    ) {}

    public function updateWaypoint(): Waypoint
    {
        return UpdateWaypointAction::run($this->waypoint);
    }
}
