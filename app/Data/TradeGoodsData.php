<?php

declare(strict_types=1);

namespace App\Data;

use App\Enums\ActivityLevels;
use App\Enums\SupplyLevels;
use App\Interfaces\GeneratableFromResponse;
use App\Traits\HasCollectionFromResponse;
use Spatie\LaravelData\Data;

class TradeGoodsData extends Data implements GeneratableFromResponse
{
    use HasCollectionFromResponse;

    public function __construct(
        public string $symbol,
        public int $tradeVolume,
        public string $supplyLevel,
        public ?string $activity,
        public int $purchasePrice,
        public int $sellPrice,
    ) {
        match (true) {
            !SupplyLevels::isValid($supplyLevel) => throw new \InvalidArgumentException("Invalid supply: {$supplyLevel}"),
            $activity && !ActivityLevels::isValid($activity) => throw new \InvalidArgumentException("Invalid activity: {$activity}"),
            default => null,
        };
    }

    public static function fromResponse(array $response): static
    {
        return new static(
            symbol: $response['symbol'],
            tradeVolume: $response['tradeVolume'],
            supplyLevel: $response['supply'],
            activity: data_get($response, 'activity'),
            purchasePrice: $response['purchasePrice'],
            sellPrice: $response['sellPrice'],
        );
    }
}
