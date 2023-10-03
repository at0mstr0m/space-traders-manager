<?php

namespace App\Data;

use Spatie\LaravelData\Data;
use App\Traits\HasCollectionFromResponse;

class WaypointTraitData extends Data
{
    use HasCollectionFromResponse;

    public function __construct(
        public string $symbol,
        public string $name,
        public string $description,
    ) {
    }
}
