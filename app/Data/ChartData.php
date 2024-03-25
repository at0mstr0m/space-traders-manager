<?php

declare(strict_types=1);

namespace App\Data;

use App\Data\Casts\CarbonCast;
use Illuminate\Support\Carbon;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Data;

class ChartData extends Data
{
    public function __construct(
        #[MapInputName('submittedBy')]
        public string $submittedBy,
        #[MapInputName('submittedOn')]
        #[WithCast(CarbonCast::class)]
        public Carbon $submittedOn,
        #[MapInputName('waypointSymbol')]
        public ?string $waypointSymbol = null,
    ) {}
}
