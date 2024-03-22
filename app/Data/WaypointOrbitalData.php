<?php

declare(strict_types=1);

namespace App\Data;

use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Data;

class WaypointOrbitalData extends Data
{
    public function __construct(
        #[MapInputName('symbol')]
        public string $symbol,
    ) {}
}
