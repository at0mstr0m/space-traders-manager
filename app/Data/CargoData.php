<?php

namespace App\Data;

use Spatie\LaravelData\Data;

class CargoData extends Data
{
    public function __construct(
        public string $symbol,
        public string $name,
        public string $description,
        public int $units,
    ) {}
}
