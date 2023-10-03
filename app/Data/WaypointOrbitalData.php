<?php

namespace App\Data;

use Spatie\LaravelData\Data;
use App\Traits\HasCollectionFromResponse;

class WaypointOrbitalData extends Data
{
    use HasCollectionFromResponse;

    public function __construct(
        public string $symbol,
    ) {
    }
}
