<?php

declare(strict_types=1);

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
