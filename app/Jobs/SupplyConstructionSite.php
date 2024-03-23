<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Data\ConstructionMaterialData;
use App\Data\ConstructionSiteData;
use App\Enums\SupplyLevels;
use App\Helpers\LocationHelper;
use App\Models\TradeOpportunity;
use Illuminate\Contracts\Queue\ShouldBeUniqueUntilProcessing;

class SupplyConstructionSite extends ShipJob implements ShouldBeUniqueUntilProcessing
{
    private ?ConstructionSiteData $constructionSite = null;

    private ?array $supplyRouteData = [];

    /**
     * Get the unique ID for the job.
     */
    public function uniqueId(): string
    {
        return static::class . ':' . $this->shipSymbol;
    }

    protected function handleShip(): void
    {
        if ($this->ship->agent->credits < 3_000_000) {
            dump("{$this->ship->symbol} Agent has less than 3.000.000 credits, aborting.");

            return;
        }

        $systemSymbol = LocationHelper::parseSystemSymbol($this->ship->waypoint_symbol);

        $this->constructionSite = LocationHelper::getWaypointUnderConstructionInSystem($systemSymbol);

        if ($this->ship->waypoint_symbol === $this->constructionSite?->waypointSymbol && !$this->ship->cargo_is_empty) {
            dump("{$this->ship->symbol} supplying cargo to construction site");
            $this->ship->supplyCargoToConstructionSite();
        }

        $this->initSupplyRouteData();

        if (!$this->supplyRouteData) {
            dump("{$this->ship->symbol} no Supply Route available.");

            return;
        }

        if ($this->ship->cargo_is_empty) {
            if ($this->ship->waypoint_symbol === $this->supplyRouteData['waypoint_symbol']) {
                dump("{$this->ship->symbol} purchasing {$this->supplyRouteData['symbol']->value} at {$this->ship->waypoint_symbol} for the construction site");
                while (!$this->ship->refresh()->is_fully_loaded) {
                    $this->ship->purchaseCargo(
                        $this->supplyRouteData['symbol'],
                        min($this->supplyRouteData['trade_volume'], $this->ship->available_cargo_capacity)
                    );
                }
                dump("{$this->ship->symbol} is fully loaded, flying to construction site");
                $this->flyToLocation($this->constructionSite->waypointSymbol);

                return;
            }
            $this->flyToLocation($this->supplyRouteData['waypoint_symbol']);

            return;
        }

        if ($this->ship->waypoint_symbol === $this->constructionSite->waypointSymbol) {
            dump("{$this->ship->symbol} is empty, flying to supply route");
            $this->flyToLocation($this->supplyRouteData['waypoint_symbol']);

            return;
        }

        if ($this->ship->waypoint_symbol !== $this->constructionSite->waypointSymbol) {
            dump("{$this->ship->symbol} is loaded, flying to construction site");
            $this->flyToLocation($this->constructionSite->waypointSymbol);

            return;
        }

        dump("{$this->ship->symbol} did nothing.");
    }

    private function initSupplyRouteData(): void
    {
        $this->supplyRouteData = $this->constructionSite
            ->constructionMaterial
            ->filter(
                fn (ConstructionMaterialData $constructionMaterialData) => $constructionMaterialData->unitsFulfilled < $constructionMaterialData->unitsRequired
            )
            ->map(
                fn (ConstructionMaterialData $constructionMaterialData) => TradeOpportunity::exports()
                    ->bySymbol($constructionMaterialData->tradeSymbol)
                    ->whereNotIn('supply', [SupplyLevels::SCARCE, SupplyLevels::LIMITED])
                    ->select('symbol', 'waypoint_symbol', 'sell_price', 'trade_volume')
                    ->get()
                    ->map(fn (TradeOpportunity $tradeOpportunity) => [
                        ...$tradeOpportunity->only('symbol', 'waypoint_symbol', 'sell_price', 'trade_volume'),
                        'distance' => LocationHelper::distance(
                            $tradeOpportunity->waypoint_symbol,
                            $this->ship->waypoint_symbol
                        ),
                        'distance_to_construction_site' => LocationHelper::distance(
                            $tradeOpportunity->waypoint_symbol,
                            $this->constructionSite->waypointSymbol
                        ),
                    ])
            )
            ->flatten(1)
            ->filter(fn (array $tradeOpportunity) => $tradeOpportunity['distance'] <= $this->ship->fuel_capacity && $tradeOpportunity['distance_to_construction_site'] <= $this->ship->fuel_capacity)
            ->sortBy('sell_price')
            ->first();
    }
}
