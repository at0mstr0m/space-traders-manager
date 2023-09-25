<?php

namespace App\Data;

use Spatie\LaravelData\Data;
use App\Enums\FactionSymbols;
use InvalidArgumentException;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\Attributes\DataCollectionOf;

class FactionData extends Data
{
    public function __construct(
        public string $symbol,
        public string $name,
        public string $description,
        public string $headquarters,
        public string $isRecruiting,
        #[DataCollectionOf(FactionTraitData::class)]
        public ?DataCollection $traits = null,
    ) {
        if (!FactionSymbols::isValid($symbol)) {
            throw new InvalidArgumentException("Invalid Faction symbol: {$symbol}");
        }
    }
}
