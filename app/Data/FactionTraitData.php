<?php

declare(strict_types=1);

namespace App\Data;

use App\Enums\FactionTraits;
use App\Traits\HasCollectionFromResponse;
use Spatie\LaravelData\Data;

class FactionTraitData extends Data
{
    use HasCollectionFromResponse;

    public function __construct(
        public string $symbol,
        public string $name,
        public string $description,
    ) {
        if (!FactionTraits::isValid($symbol)) {
            throw new \InvalidArgumentException("Invalid Faction Trait symbol: {$symbol}");
        }
    }
}
