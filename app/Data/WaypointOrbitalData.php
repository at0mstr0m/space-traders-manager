<?php

declare(strict_types=1);

namespace App\Data;

use App\Traits\HasCollectionFromResponse;
use Spatie\LaravelData\Data;

class WaypointOrbitalData extends Data
{
    use HasCollectionFromResponse;

    public function __construct(
        public string $symbol,
    ) {}
}
