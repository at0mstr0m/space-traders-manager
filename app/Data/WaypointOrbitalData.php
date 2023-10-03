<?php

namespace App\Data;

use Spatie\LaravelData\Data;

class WaypointOrbitalData extends Data
{
    public function __construct(
        public string $symbol,
    ) {}
}
