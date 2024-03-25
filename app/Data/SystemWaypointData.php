<?php

declare(strict_types=1);

namespace App\Data;

use App\Enums\WaypointTypes;
use Illuminate\Support\Arr;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Casts\EnumCast;
use Spatie\LaravelData\Data;

class SystemWaypointData extends Data
{
    public function __construct(
        #[MapInputName('symbol')]
        public string $symbol,
        #[MapInputName('cooldown.expiration')]
        #[WithCast(EnumCast::class)]
        public WaypointTypes $type,
        #[MapInputName('x')]
        public int $x,
        #[MapInputName('y')]
        public int $y,
        #[MapInputName('orbitals')]
        public array $orbitals,
    ) {
        $this->orbitals = Arr::pluck($orbitals, 'symbol');
    }
}
