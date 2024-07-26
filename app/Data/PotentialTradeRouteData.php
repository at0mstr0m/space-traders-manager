<?php

declare(strict_types=1);

namespace App\Data;

use App\Enums\SupplyLevels;
use App\Enums\TradeSymbols;
use App\Traits\DataHasModel;
use Spatie\LaravelData\Data;

/**
 * @deprecated
 */
class PotentialTradeRouteData extends Data
{
    use DataHasModel;

    public function __construct(
        public string $tradeSymbol,
        public string $origin,
        public string $destination,
        public ?int $purchasePrice,
        public ?string $supplyAtOrigin,
        public ?string $activityAtOrigin,
        public ?int $tradeVolumeAtOrigin,
        public ?int $sellPrice,
        public ?string $supplyAtDestination,
        public ?string $activityAtDestination,
        public ?int $tradeVolumeAtDestination,
    ) {
        match (true) {
            $tradeSymbol && !TradeSymbols::isValid($tradeSymbol) => throw new \InvalidArgumentException("Invalid trade symbol: {$tradeSymbol}"),
            $supplyAtOrigin && !SupplyLevels::isValid($supplyAtOrigin) => throw new \InvalidArgumentException("Invalid supply at origin: {$supplyAtOrigin}"),
            $supplyAtDestination && !SupplyLevels::isValid($supplyAtDestination) => throw new \InvalidArgumentException("Invalid supply at destination: {$supplyAtDestination}"),
            default => null,
        };
    }

    public static function fromAggregatedData(array $data): static
    {
        $tradeSymbol = $data['symbol'];
        $exportingMarket = $data['exportingMarket'];
        $importingMarket = $data['importingMarket'];

        /** @var ?TradeGoodsData */
        $exportTradeGoodData = $exportingMarket->tradeGoods
            ->firstWhere('symbol', $tradeSymbol);
        /** @var ?TradeGoodsData */
        $importTradeGoodData = $importingMarket->tradeGoods
            ->toCollection()
            ->firstWhere('symbol', $tradeSymbol);

        return new static(
            tradeSymbol: $tradeSymbol,
            origin: $exportingMarket->symbol,
            destination: $importingMarket->symbol,
            purchasePrice: $exportTradeGoodData?->purchasePrice,
            supplyAtOrigin: $exportTradeGoodData?->supplyLevel,
            activityAtOrigin: $exportTradeGoodData?->activityLevel,
            tradeVolumeAtOrigin: $exportTradeGoodData?->tradeVolume,
            sellPrice: $importTradeGoodData?->purchasePrice,
            supplyAtDestination: $importTradeGoodData?->supplyLevel,
            activityAtDestination: $importTradeGoodData?->activityLevel,
            tradeVolumeAtDestination: $importTradeGoodData?->tradeVolume,
        );
    }
}
