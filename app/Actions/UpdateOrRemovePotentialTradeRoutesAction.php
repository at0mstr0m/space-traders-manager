<?php

declare(strict_types=1);

namespace App\Actions;

use App\Data\PotentialTradeRouteData;
use App\Helpers\LocationHelper;
use App\Helpers\SpaceTraders;
use App\Models\PotentialTradeRoute;
use Illuminate\Support\Collection;
use Lorisleiva\Actions\Concerns\AsAction;

class UpdateOrRemovePotentialTradeRoutesAction
{
    use AsAction;

    public function handle(): void
    {
        /** @var SpaceTraders $api */
        $api = app(SpaceTraders::class);

        $changedIds = collect();

        LocationHelper::systemsWithShips()
            ->reduce(
                fn (Collection $carry, string $systemSymbol) => $carry->concat(
                    $api->listPotentialTradeRoutesInSystem($systemSymbol)
                ),
                collect()
            )->each(
                fn (PotentialTradeRouteData $potentialTradeRoute) => $changedIds->add(PotentialTradeRoute::updateOrCreate(
                    [
                        'trade_symbol' => $potentialTradeRoute->tradeSymbol,
                        'origin' => $potentialTradeRoute->origin,
                        'destination' => $potentialTradeRoute->destination,
                    ],
                    [
                        'purchase_price' => $potentialTradeRoute->purchasePrice,
                        'supply_at_origin' => $potentialTradeRoute->supplyAtOrigin,
                        'activity_at_origin' => $potentialTradeRoute->activityAtOrigin,
                        'trade_volume_at_origin' => $potentialTradeRoute->tradeVolumeAtOrigin,
                        'sell_price' => $potentialTradeRoute->sellPrice,
                        'supply_at_destination' => $potentialTradeRoute->supplyAtDestination,
                        'activity_at_destination' => $potentialTradeRoute->activityAtDestination,
                        'trade_volume_at_destination' => $potentialTradeRoute->tradeVolumeAtDestination,
                    ],
                )->id),
            );

        PotentialTradeRoute::whereNotIn('id', $changedIds)->delete();
    }
}
