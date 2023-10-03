<?php

namespace App\Data;

use App\Enums\ShipTypes;
use Spatie\LaravelData\Data;
use InvalidArgumentException;
use App\Traits\HasCollectionFromResponse;

class ShipTypeData extends Data
{
    use HasCollectionFromResponse;

    public function __construct(
        public string $type,
    ) {
        if (!ShipTypes::isValid($type)) {
            throw new InvalidArgumentException("Invalid ship type: {$type}");
        }
    }
}
