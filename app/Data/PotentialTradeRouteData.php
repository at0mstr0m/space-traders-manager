<?php

namespace App\Data;

use App\Enums\Supplies;
use App\Enums\TradeSymbols;
use App\Traits\DataHasModel;
use Spatie\LaravelData\Data;

class PotentialTradeRouteData extends Data
{
    use DataHasModel;

    public function __construct(
        public string $tradeSymbol,
        public string $origin,
        public string $destination,
        public ?int $purchasePrice,
        public ?string $supplyAtOrigin,
        public ?int $tradeVolumeAtOrigin,
        public ?int $sellPrice,
        public ?string $supplyAtDestination,
        public ?int $tradeVolumeAtDestination,
    ) {
        match (true) {
            $tradeSymbol && !TradeSymbols::isValid($tradeSymbol) => throw new \InvalidArgumentException("Invalid trade symbol: {$tradeSymbol}"),
            $supplyAtOrigin && !Supplies::isValid($supplyAtOrigin) => throw new \InvalidArgumentException("Invalid supply at origin: {$supplyAtOrigin}"),
            $supplyAtDestination && !Supplies::isValid($supplyAtDestination) => throw new \InvalidArgumentException("Invalid supply at destination: {$supplyAtDestination}"),
            default => null,
        };
    }

    public static function fromAggregatedData(array $data): self
    {
        $tradeSymbol = $data['symbol'];
        $exportingMarket = $data['exportingMarket'];
        $importingMarket = $data['importingMarket'];

        /** @var ?TradeGoodsData */
        $exportTradeGoodData = $exportingMarket->tradeGoods
            ->toCollection()
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
            supplyAtOrigin: $exportTradeGoodData?->supply,
            tradeVolumeAtOrigin: $exportTradeGoodData?->tradeVolume,
            sellPrice: $importTradeGoodData?->purchasePrice,
            supplyAtDestination: $importTradeGoodData?->supply,
            tradeVolumeAtDestination: $importTradeGoodData?->tradeVolume,
        );
    }
}
