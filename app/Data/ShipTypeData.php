<?php

declare(strict_types=1);

namespace App\Data;

use App\Enums\ShipTypes;
use App\Traits\HasCollectionFromResponse;
use Spatie\LaravelData\Data;

class ShipTypeData extends Data
{
    use HasCollectionFromResponse;

    public function __construct(
        public string $type,
    ) {
        if (!ShipTypes::isValid($type)) {
            throw new \InvalidArgumentException("Invalid ship type: {$type}");
        }
    }
}
