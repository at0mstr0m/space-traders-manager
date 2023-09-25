<?php

namespace App\Data;

use App\Enums\FactionTraits;
use Spatie\LaravelData\Data;
use InvalidArgumentException;

class FactionTraitData extends Data
{
    public function __construct(
        public string $symbol,
        public string $name,
        public string $description,
    ) {
        if (!FactionTraits::isValid($symbol)) {
            throw new InvalidArgumentException("Invalid Faction Trait symbol: {$symbol}");
        }
    }
}
