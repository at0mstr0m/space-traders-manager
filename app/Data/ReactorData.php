<?php

declare(strict_types=1);

namespace App\Data;

use App\Enums\ReactorSymbols;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Casts\EnumCast;
use Spatie\LaravelData\Data;

class ReactorData extends Data
{
    public function __construct(
        #[MapInputName('symbol')]
        #[WithCast(EnumCast::class)]
        public ReactorSymbols $symbol,
        #[MapInputName('name')]
        public string $name,
        #[MapInputName('description')]
        public string $description,
        #[MapInputName('condition')]
        public float $condition,
        #[MapInputName('integrity')]
        public float $integrity,
        #[MapInputName('powerOutput')]
        public int $powerOutput,
        #[MapInputName('requirements.crew')]
        public int $requiredCrew,
    ) {}
}
