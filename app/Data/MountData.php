<?php

declare(strict_types=1);

namespace App\Data;

use App\Enums\MountSymbols;
use App\Traits\HasCollectionFromResponse;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Casts\EnumCast;
use Spatie\LaravelData\Data;

class MountData extends Data
{
    use HasCollectionFromResponse;

    /**
     * @param Collection<int, DepositData> $deposits
     */
    public function __construct(
        #[MapInputName('symbol')]
        #[WithCast(EnumCast::class)]
        public MountSymbols $symbol,
        #[MapInputName('name')]
        public string $name,
        #[MapInputName('description')]
        public string $description,
        #[MapInputName('requirements.power')]
        public int $requiredPower,
        #[MapInputName('requirements.crew')]
        public int $requiredCrew,
        #[MapInputName('strength')]
        public ?int $strength = null,
        #[MapInputName('deposits')]
        public Collection $deposits,
    ) {}
}
