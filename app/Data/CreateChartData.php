<?php

declare(strict_types=1);

namespace App\Data;

use Spatie\LaravelData\Data;
use App\Interfaces\GeneratableFromResponse;

class CreateChartData extends Data implements GeneratableFromResponse
{
    public function __construct(
        public ChartData $chart,
        public WaypointData $waypoint,
    ) {
    }

    public static function fromResponse(array $response): static
    {
        return new static(
            chart: ChartData::fromResponse($response['chart']),
            waypoint: WaypointData::fromResponse($response['waypoint']),
        );
    }
}
