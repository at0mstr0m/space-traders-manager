<?php

declare(strict_types=1);

namespace App\Data;

use App\Enums\FrameSymbols;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Casts\EnumCast;
use Spatie\LaravelData\Data;

class FrameData extends Data
{
    public function __construct(
        #[MapInputName('symbol')]
        #[WithCast(EnumCast::class)]
        public FrameSymbols $symbol,
        #[MapInputName('name')]
        public string $name,
        #[MapInputName('description')]
        public string $description,
        #[MapInputName('moduleSlots')]
        public int $moduleSlots,
        #[MapInputName('mountingPoints')]
        public int $mountingPoints,
        #[MapInputName('fuelCapacity')]
        public int $fuelCapacity,
        #[MapInputName('condition')]
        public float $condition,
        #[MapInputName('integrity')]
        public float $integrity,
        #[MapInputName('requirements.power')]
        public int $requiredPower,
        #[MapInputName('requirements.crew')]
        public int $requiredCrew,
    ) {}
}
