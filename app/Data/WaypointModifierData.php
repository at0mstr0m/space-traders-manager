<?php

declare(strict_types=1);

namespace App\Data;

use App\Enums\WaypointModifierSymbols;
use App\Traits\HasCollectionFromResponse;
use Spatie\LaravelData\Data;

class WaypointModifierData extends Data
{
    use HasCollectionFromResponse;

    public function __construct(
        public string $symbol,
        public string $name,
        public string $description,
    ) {
        match (true) {
            !WaypointModifierSymbols::isValid($symbol) => throw new \InvalidArgumentException("Invalid waypoint modifier symbol: {$symbol}"),
            default => null,
        };
    }
}
