<?php

declare(strict_types=1);

namespace App\Data;

use App\Data\Casts\CarbonCast;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Data;

class SurveyData extends Data
{
    /**
     * @param Collection<int, DepositData> $deposits
     */
    public function __construct(
        #[MapInputName('signature')]
        public string $signature,
        #[MapInputName('symbol')]
        public string $waypointSymbol,
        #[MapInputName('expiration')]
        #[WithCast(CarbonCast::class)]
        public Carbon $expiration,
        #[MapInputName('size')]
        public string $size,
        #[MapInputName('rawResponse')]
        public string $rawResponse,
        #[MapInputName('deposits')]
        public ?Collection $deposits = null,
    ) {}
}
