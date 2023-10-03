<?php

namespace App\Data;

use Spatie\LaravelData\Data;

class WaypointTraitData extends Data
{
    public function __construct(
        public string $symbol,
        public string $name,
        public string $description,
    ) {}
}
