<?php

declare(strict_types=1);

namespace App\Data;

use App\Enums\ModuleSymbols;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Casts\EnumCast;
use Spatie\LaravelData\Data;

class ModuleData extends Data
{
    public function __construct(
        #[MapInputName('symbol')]
        #[WithCast(EnumCast::class)]
        public ModuleSymbols $symbol,
        #[MapInputName('name')]
        public string $name,
        #[MapInputName('description')]
        public string $description,
        #[MapInputName('requirements.power')]
        public int $requiredPower,
        #[MapInputName('requirements.crew')]
        public int $requiredCrew,
        #[MapInputName('requirements.slots')]
        public int $requiredSlots,
        #[MapInputName('capacity')]
        public ?int $capacity = null,
        #[MapInputName('range')]
        public ?int $range = null,
    ) {}
}
