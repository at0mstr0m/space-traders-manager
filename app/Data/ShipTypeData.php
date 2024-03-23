<?php

declare(strict_types=1);

namespace App\Data;

use App\Enums\ShipTypes;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Casts\EnumCast;
use Spatie\LaravelData\Data;

class ShipTypeData extends Data
{
    public function __construct(
        #[MapInputName('type')]
        #[WithCast(EnumCast::class)]
        public ShipTypes $type,
    ) {}
}
