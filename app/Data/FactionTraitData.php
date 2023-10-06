<?php

declare(strict_types=1);

namespace App\Data;

use App\Enums\FactionTraits;
use Spatie\LaravelData\Data;
use InvalidArgumentException;
use App\Traits\HasCollectionFromResponse;

class FactionTraitData extends Data
{
    use HasCollectionFromResponse;

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
