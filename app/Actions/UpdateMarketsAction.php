<?php

declare(strict_types=1);

namespace App\Actions;

use App\Data\MarketData;
use App\Enums\WaypointTraitSymbols;
use App\Helpers\SpaceTraders;
use App\Models\TradeOpportunity;
use App\Models\Waypoint;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Collection;
use Lorisleiva\Actions\Concerns\AsAction;

class UpdateMarketsAction implements ShouldQueue, ShouldBeUnique
{
    use AsAction;

    public function handle(?string $systemSymbol = null): void
    {
        /** @var SpaceTraders $api */
        $api = app(SpaceTraders::class);

        $changedIds = Waypoint::query()
            ->whereRelation('traits', 'symbol', WaypointTraitSymbols::MARKETPLACE)
            ->where(
                fn (Builder $query) => $query->whereDoesntHave('marketGoods')
                    // only fetch markets again that have ships present
                    // otherwise all jump gates are fetched again
                    ->orWhere(
                        fn (Builder $query) => $query
                            ->whereHas('marketGoods')
                            ->onlyHavingShipPresent()
                    )
            )
            ->when(
                $systemSymbol,
                fn (Builder $query) => $query->where('system_symbol', $systemSymbol)
            )
            ->pluck('symbol')
            ->map(fn (string $waypointSymbol) => $api->getMarket($waypointSymbol))
            ->reduce(
                fn (Collection $result, MarketData $marketData) => $result
                    ->concat(UpdateMarketAction::run($marketData)),
                collect()
            );

        if (!$systemSymbol) {
            TradeOpportunity::whereNotIn('id', $changedIds)->delete();
        }

        // relies solely on the data just fetched
        UpdateOrRemovePotentialTradeRoutesAction::dispatchSync();
    }
}
