<?php

declare(strict_types=1);

namespace App\Actions;

use App\Data\ImportExportExchangeGoodData;
use App\Data\MarketData;
use App\Data\TradeGoodsData;
use App\Enums\TradeGoodTypes;
use App\Models\TradeOpportunity;
use App\Models\Waypoint;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Collection;
use Lorisleiva\Actions\Concerns\AsAction;

class UpdateMarketAction implements ShouldQueue, ShouldBeUnique
{
    use AsAction;

    public function handle(
        MarketData $marketData,
        bool $updateTradeRoutes = false
    ): Collection {
        $waypointSymbol = $marketData->symbol;
        $changedIds = $marketData->tradeGoods?->map(
            fn (TradeGoodsData $goodData): int => TradeOpportunity::updateOrCreate(
                [
                    'waypoint_symbol' => $waypointSymbol,
                    'symbol' => $goodData->symbol,
                    'type' => $goodData->tradeGoodType,
                ],
                [
                    'purchase_price' => $goodData->purchasePrice,
                    'sell_price' => $goodData->sellPrice,
                    'trade_volume' => $goodData->tradeVolume,
                    'supply' => $goodData->supplyLevel,
                    'activity' => $goodData->activityLevel,
                ]
            )->id
        ) ?? collect();

        if ($updateTradeRoutes && $changedIds->isNotEmpty()) {
            UpdateOrRemovePotentialTradeRoutesAction::dispatchSync();
        }

        $waypoint = Waypoint::findBySymbol($waypointSymbol);

        // market goods traded at waypoint never change
        if ($waypoint->marketGoods()->exists()) {
            return $changedIds;
        }

        foreach ([
            'exports' => TradeGoodTypes::EXPORT,
            'imports' => TradeGoodTypes::IMPORT,
            'exchange' => TradeGoodTypes::EXCHANGE,
        ] as $key => $type) {
            $waypoint->marketGoods()->createMany(
                $marketData->{$key}->map(fn (ImportExportExchangeGoodData $goodData) => [
                    'type' => $type,
                    'trade_symbol' => $goodData->symbol,
                ])
            );
        }

        return $changedIds;
    }
}
