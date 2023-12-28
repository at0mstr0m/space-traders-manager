<?php

declare(strict_types=1);

namespace App\Data;

use App\Enums\ContractTypes;
use App\Enums\FactionSymbols;
use App\Interfaces\GeneratableFromResponse;
use Illuminate\Support\Carbon;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;

class ContractData extends Data implements GeneratableFromResponse
{
    public function __construct(
        public string $identification,
        public string $factionSymbol,
        public string $type,
        public bool $accepted,
        public bool $fulfilled,
        public Carbon $deadline,
        public Carbon $deadlineToAccept,
        public int $paymentOnAccepted,
        public int $paymentOnFulfilled,
        #[DataCollectionOf(DeliveryData::class)]
        public ?DataCollection $deliveries = null,
    ) {
        match (true) {
            !FactionSymbols::isValid($factionSymbol) => throw new \InvalidArgumentException("Invalid faction symbol: {$factionSymbol}"),
            !ContractTypes::isValid($type) => throw new \InvalidArgumentException("Invalid contract type: {$type}"),
            default => null,
        };
    }

    public static function fromResponse(array $response): static
    {
        return new static(
            identification: $response['id'],
            factionSymbol: $response['factionSymbol'],
            type: $response['type'],
            accepted: $response['accepted'],
            fulfilled: $response['fulfilled'],
            deadline: Carbon::parse($response['terms']['deadline']),
            deadlineToAccept: Carbon::parse($response['deadlineToAccept']),
            paymentOnAccepted: $response['terms']['payment']['onAccepted'],
            paymentOnFulfilled: $response['terms']['payment']['onFulfilled'],
            deliveries: DeliveryData::collectionFromResponse($response['terms']['deliver']),
        );
    }
}
