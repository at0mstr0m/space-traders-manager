<?php

declare(strict_types=1);

namespace App\Data;

use App\Enums\Supplies;
use Spatie\LaravelData\Data;
use InvalidArgumentException;
use App\Traits\HasCollectionFromResponse;

class TradeGoodsData extends Data
{
    use HasCollectionFromResponse;

    public function __construct(
        public string $symbol,
        public int $tradeVolume,
        public string $supply,
        public int $purchasePrice,
        public int $sellPrice,
    ) {
        match (true) {
            !Supplies::isValid($supply) => throw new InvalidArgumentException("Invalid supply: {$supply}"),
            default => null,
        };
    }

    public static function fromResponse(array $response): static
    {
        return new self(
            symbol: $response['symbol'],
            tradeVolume: $response['tradeVolume'],
            supply: $response['supply'],
            purchasePrice: $response['purchasePrice'],
            sellPrice: $response['sellPrice'],
        );
    }
}
