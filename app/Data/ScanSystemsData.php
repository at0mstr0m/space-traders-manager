<?php

declare(strict_types=1);

namespace App\Data;

use App\Interfaces\GeneratableFromResponse;
use Illuminate\Support\Carbon;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;

class ScanSystemsData extends Data implements GeneratableFromResponse
{
    public function __construct(
        public Carbon $cooldown,
        #[DataCollectionOf(ScannedSystemData::class)]
        public ?DataCollection $systems = null,
    ) {}

    public static function fromResponse(array $response): static
    {
        return new static(
            cooldown: Carbon::parse($response['cooldown']['expiration']),
            systems: ScannedSystemData::collectionFromResponse($response['systems']),
        );
    }
}
