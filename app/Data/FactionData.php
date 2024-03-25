<?php

declare(strict_types=1);

namespace App\Data;

use App\Enums\FactionSymbols;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Casts\EnumCast;
use Spatie\LaravelData\Data;

class FactionData extends Data
{
    /**
     * @param Collection<int, FactionTraitData> $traits
     */
    public function __construct(
        #[MapInputName('symbol')]
        #[WithCast(EnumCast::class)]
        public FactionSymbols $symbol,
        #[MapInputName('name')]
        public string $name,
        #[MapInputName('description')]
        public string $description,
        #[MapInputName('headquarters')]
        public string $headquarters,
        #[MapInputName('isRecruiting')]
        public bool $isRecruiting,
        #[MapInputName('traits')]
        public Collection $traits,
    ) {}
}
