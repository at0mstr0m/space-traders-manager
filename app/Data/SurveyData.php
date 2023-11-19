<?php

namespace App\Data;

use App\Interfaces\GeneratableFromResponse;
use App\Traits\HasCollectionFromResponse;
use Illuminate\Support\Carbon;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;

class SurveyData extends Data implements GeneratableFromResponse
{
    use HasCollectionFromResponse;

    public function __construct(
        public string $waypointSymbol,
        public string $signature,
        #[DataCollectionOf(DepositData::class)]
        public ?DataCollection $deposits = null,
        public Carbon $expiration,
        public string $size,
    ) {}

    public static function fromResponse(array $response): static
    {
        return new static(
            waypointSymbol: $response['symbol'],
            signature: $response['signature'],
            deposits: DepositData::collectionFromResponse(data_get($response, 'deposits', [])),
            expiration: Carbon::parse($response['expiration']),
            size: $response['size'],
        );
    }
}
