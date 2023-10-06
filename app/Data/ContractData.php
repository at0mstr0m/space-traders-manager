<?php

declare(strict_types=1);

namespace App\Data;

use App\Data\DeliveryData;
use App\Enums\ContractTypes;
use Spatie\LaravelData\Data;
use App\Enums\FactionSymbols;
use InvalidArgumentException;
use Illuminate\Support\Carbon;
use Spatie\LaravelData\DataCollection;
use App\Interfaces\GeneratableFromResponse;
use Spatie\LaravelData\Attributes\DataCollectionOf;

class ContractData extends Data implements GeneratableFromResponse
{
    public function __construct(
        public string $identification,
        public string $factionSymbol,
        public string $type,
        public bool $fulfilled,
        public Carbon $deadline,
        public Carbon $deadlineToAccept,
        public int $paymentOnAccepted,
        public int $paymentOnFulfilled,
        #[DataCollectionOf(DeliveryData::class)]
        public ?DataCollection $deliveries = null,
    ) {
        match (true) {
            !FactionSymbols::isValid($factionSymbol) => throw new InvalidArgumentException("Invalid faction symbol: {$factionSymbol}"),
            !ContractTypes::isValid($type) => throw new InvalidArgumentException("Invalid contract type: {$type}"),
            default => null,
        };
    }

    public static function fromResponse(array $response): static
    {
        return new static(
            identification: $response['id'],
            factionSymbol: $response['factionSymbol'],
            type: $response['type'],
            fulfilled: $response['fulfilled'],
            deadline: Carbon::parse($response['terms']['deadline']),
            deadlineToAccept: Carbon::parse($response['deadlineToAccept']),
            paymentOnAccepted: $response['terms']['payment']['onAccepted'],
            paymentOnFulfilled: $response['terms']['payment']['onFulfilled'],
            deliveries: DeliveryData::collectionFromResponse($response['terms']['deliver']),
        );
    }
}
