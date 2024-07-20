<?php

declare(strict_types=1);

namespace App\Actions;

use App\Data\MarketData;
use App\Enums\ShipNavStatus;
use App\Enums\WaypointTraitSymbols;
use App\Helpers\SpaceTraders;
use App\Models\TradeOpportunity;
use App\Models\Waypoint;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Lorisleiva\Actions\Concerns\AsAction;

class UpdateMarketsAction implements ShouldQueue, ShouldBeUnique
{
    use AsAction;

    public function handle(): void
    {
        /** @var SpaceTraders $api */
        $api = app(SpaceTraders::class);

        $changedIds = collect();

        Waypoint::query()
            ->whereRelation('ships', 'status', '<>', ShipNavStatus::IN_TRANSIT)
            ->whereRelation('traits', 'symbol', WaypointTraitSymbols::MARKETPLACE)
            ->get()
            ->pluck('symbol')
            ->map(fn (string $waypointSymbol) => $api->getMarket($waypointSymbol))
            ->each(fn (MarketData $marketData) => $changedIds
                ->concat(UpdateMarketAction::run($marketData)));

        TradeOpportunity::whereNotIn('id', $changedIds)->delete();

        // relies solely on the data just fetched
        UpdateOrRemovePotentialTradeRoutesAction::dispatchSync();
    }
}
