<?php

namespace App\Data;

use App\Enums\MountSymbols;
use Illuminate\Support\Arr;
use Spatie\LaravelData\Data;
use InvalidArgumentException;
use Spatie\LaravelData\DataCollection;
use App\Interfaces\GeneratableFromResponse;
use Spatie\LaravelData\Attributes\DataCollectionOf;

class MountData extends Data implements GeneratableFromResponse
{
    public function __construct(
        public string $symbol,
        public string $name,
        public string $description,
        public ?int $strength = null,
        #[DataCollectionOf(DepositData::class)]
        public ?DataCollection $deposits = null,
        public int $requiredPower,
        public int $requiredCrew,
    ) {
        if (!MountSymbols::isValid($symbol)) {
            throw new InvalidArgumentException("Invalid mount symbol: {$symbol}");
        }
    }

    public static function fromResponse(array $response): static
    {
        return new self(
            symbol: $response['symbol'],
            name: $response['name'],
            description: $response['description'],
            strength: data_get($response, 'strength'),
            deposits: DepositData::collection(
                Arr::map($response['deposits'] ?? [],
                fn (string $deposit) => DepositData::from(['symbol' => $deposit]))
            ),
            requiredPower: $response['requirements']['power'],
            requiredCrew: $response['requirements']['crew'],
        );
    }
}
