<?php

declare(strict_types=1);

namespace App\Data;

use Spatie\LaravelData\Data;
use Illuminate\Support\Carbon;
use Spatie\LaravelData\DataCollection;
use App\Interfaces\GeneratableFromResponse;
use Spatie\LaravelData\Attributes\DataCollectionOf;

class ScanWaypointsData extends Data implements GeneratableFromResponse
{
    public function __construct(
        public Carbon $cooldown,
        #[DataCollectionOf(ScannedWaypointData::class)]
        public ?DataCollection $waypoints = null,
    ) {
    }

    public static function fromResponse(array $response): static
    {
        return new self(
            cooldown: Carbon::parse($response['cooldown']['expiration']),
            waypoints: ScannedWaypointData::collectionFromResponse($response['waypoints']),
        );
    }
}
