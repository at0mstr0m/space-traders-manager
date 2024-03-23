<?php

declare(strict_types=1);

namespace App\Actions;

use App\Data\TradeGoodsData;
use App\Helpers\LocationHelper;
use App\Helpers\SpaceTraders;
use App\Models\TradeOpportunity;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Collection;
use Lorisleiva\Actions\Concerns\AsAction;

class UpdateOrRemoveTradeOpportunitiesAction implements ShouldQueue, ShouldBeUnique
{
    use AsAction;

    public function handle(): void
    {
        /** @var SpaceTraders $api */
        $api = app(SpaceTraders::class);

        $changedIds = collect();

        LocationHelper::systemsWithShips()
            ->map(
                fn (string $systemSymbol) => $api->listTradeGoodsInSystem($systemSymbol)
            )->reduce(
                fn (Collection $carry, Collection $marketData) => $carry->merge($marketData),
                collect()
            )->each(
                fn (Collection $marketData, string $waypointSymbol) => $marketData->each(
                    fn (TradeGoodsData $goodData) => $changedIds->add(
                        TradeOpportunity::updateOrCreate(
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
                    )
                )
            );

        TradeOpportunity::whereNotIn('id', $changedIds)->delete();

        // relies solely on the data just fetched
        UpdateOrRemovePotentialTradeRoutesAction::dispatchSync();
    }
}
