<?php

declare(strict_types=1);

namespace App\Data;

use App\Enums\EngineSymbols;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Casts\EnumCast;
use Spatie\LaravelData\Data;

class EngineData extends Data
{
    public function __construct(
        #[MapInputName('symbol')]
        #[WithCast(EnumCast::class)]
        public EngineSymbols $symbol,
        #[MapInputName('name')]
        public string $name,
        #[MapInputName('description')]
        public string $description,
        #[MapInputName('condition')]
        public float $condition,
        #[MapInputName('integrity')]
        public float $integrity,
        #[MapInputName('speed')]
        public int $speed,
        #[MapInputName('requirements.power')]
        public int $requiredPower,
        #[MapInputName('requirements.crew')]
        public int $requiredCrew,
    ) {}
}
