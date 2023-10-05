<?php

declare(strict_types=1);

namespace App\Data;

use Spatie\LaravelData\Data;

class NavigateShipData extends Data
{
    public function __construct(
        public FuelData $fuel,
        public NavigationData $nav,
    ) {
    }

    public static function fromResponse(array $response): static
    {
        return new self(
            fuel: FuelData::fromResponse($response['fuel']),
            nav: NavigationData::fromResponse($response['fuel']),
        );
    }
}
