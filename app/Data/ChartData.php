<?php

declare(strict_types=1);

namespace App\Data;

use Spatie\LaravelData\Data;
use App\Interfaces\GeneratableFromResponse;
use Illuminate\Support\Carbon;

class ChartData extends Data implements GeneratableFromResponse
{
    public function __construct(
        public ?string $waypointSymbol,
        public string $submittedBy,
        public Carbon $submittedOn,
    ) {
    }

    public static function fromResponse(array $response): static
    {
        return new static(
            waypointSymbol: data_get($response, 'waypointSymbol'),
            submittedBy: $response['submittedBy'],
            submittedOn: Carbon::parse($response['submittedOn']),
        );
    }
}
