<?php

declare(strict_types=1);

namespace App\Data;

use App\Data\Casts\CollectionCast;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Data;

class JumpGateData extends Data
{
    /**
     * @param Collection<int, string> $connections
     */
    public function __construct(
        #[MapInputName('symbol')]
        public string $symbol,
        #[MapInputName('connections')]
        #[WithCast(CollectionCast::class)]
        public Collection $connections,
    ) {}
}
