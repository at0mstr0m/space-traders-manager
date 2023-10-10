<?php

declare(strict_types=1);

namespace App\Data;

use App\Enums\MountSymbols;
use Spatie\LaravelData\Data;
use InvalidArgumentException;
use Spatie\LaravelData\DataCollection;
use App\Traits\HasCollectionFromResponse;
use App\Interfaces\GeneratableFromResponse;
use Spatie\LaravelData\Attributes\DataCollectionOf;

class MountData extends Data implements GeneratableFromResponse
{
    use HasCollectionFromResponse;

    public function __construct(
        public string $symbol,
        public string $name,
        public string $description,
        public int $requiredPower,
        public int $requiredCrew,
        public ?int $strength = null,
        #[DataCollectionOf(DepositData::class)]
        public ?DataCollection $deposits = null,
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
            deposits: DepositData::collectionFromResponse(data_get($response, 'deposits', [])),
            requiredPower: $response['requirements']['power'],
            requiredCrew: $response['requirements']['crew'],
        );
    }
}
