<?php

declare(strict_types=1);

namespace App\Data;

use App\Data\Casts\CarbonCast;
use Illuminate\Support\Carbon;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Data;

class TransactionData extends Data
{
    public function __construct(
        #[MapInputName('shipSymbol')]
        public string $shipSymbol,
        #[MapInputName('waypointSymbol')]
        public string $waypointSymbol,
        #[MapInputName('agentSymbol')]
        public string $agentSymbol,
        #[MapInputName('price')]
        public int $price,
        #[MapInputName('timestamp')]
        #[WithCast(CarbonCast::class)]
        public Carbon $timestamp,
    ) {}
}
