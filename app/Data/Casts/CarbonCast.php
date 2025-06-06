<?php

declare(strict_types=1);

namespace App\Data\Casts;

use Illuminate\Support\Carbon;
use Spatie\LaravelData\Casts\Cast;
use Spatie\LaravelData\Support\Creation\CreationContext;
use Spatie\LaravelData\Support\DataProperty;

class CarbonCast implements Cast
{
    public function cast(DataProperty $property, mixed $value, array $properties, CreationContext $context): Carbon
    {
        return Carbon::parse($value);
    }
}
