<?php

declare(strict_types=1);

namespace App\Data;

use App\Interfaces\GeneratableFromResponse;
use Illuminate\Support\Carbon;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;

class ScanShipsData extends Data implements GeneratableFromResponse
{
    public function __construct(
        public Carbon $cooldown,
        #[DataCollectionOf(ScannedWaypointData::class)]
        public ?DataCollection $ships = null,
    ) {}

    public static function fromResponse(array $response): static
    {
        return new static(
            cooldown: Carbon::parse($response['cooldown']['expiration']),
            ships: ScannedShipData::collectionFromResponse($response['ships']),
        );
    }
}
