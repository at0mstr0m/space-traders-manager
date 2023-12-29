<?php

declare(strict_types=1);

namespace App\Data;

use App\Interfaces\GeneratableFromResponse;
use Illuminate\Support\Carbon;
use Spatie\LaravelData\Data;

class JumpShipData extends Data implements GeneratableFromResponse
{
    public function __construct(
        public Carbon $cooldown,
        public NavigationData $nav,
    ) {}

    public static function fromResponse(array $response): static
    {
        return new static(
            cooldown: Carbon::parse($response['cooldown']['expiration']),
            nav: NavigationData::fromResponse($response['nav']),
        );
    }
}
