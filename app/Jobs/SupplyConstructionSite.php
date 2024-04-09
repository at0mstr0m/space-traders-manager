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

    private ?TradeOpportunity $supplyTradeOpportunity = null;

    /**
     * Get the unique ID for the job.
     */
    public function uniqueId(): string
    {
        return static::class . ':' . $this->shipSymbol;
    }

    protected function handleShip(): void
    {
        if ($this->ship->agent->credits < 1_300_000) {
            dump("{$this->ship->symbol} Agent has less than 1.300.000 credits, aborting.");

            return;
        }

        $systemSymbol = LocationHelper::parseSystemSymbol($this->ship->waypoint_symbol);

        $this->constructionSite = LocationHelper::getWaypointUnderConstructionInSystem($systemSymbol);

        if ($this->ship->waypoint_symbol === $this->constructionSite?->waypointSymbol && !$this->ship->cargo_is_empty) {
            dump("{$this->ship->symbol} supplying cargo to construction site");
            $this->ship->supplyCargoToConstructionSite();
        }

        $this->initSupplyTradeOpportunity();

        if (!$this->supplyTradeOpportunity) {
            dump("{$this->ship->symbol} no Supply Route available.");

            return;
        }

        if ($this->ship->cargo_is_empty) {
            if ($this->ship->waypoint_symbol === $this->supplyTradeOpportunity->waypoint_symbol) {
                dump("{$this->ship->symbol} purchasing {$this->supplyTradeOpportunity->symbol->value} at {$this->ship->waypoint_symbol} for the construction site");
                while (!$this->ship->refresh()->is_fully_loaded) {
                    $this->ship->purchaseCargo(
                        $this->supplyTradeOpportunity->symbol,
                        min(
                            $this->supplyTradeOpportunity->trade_volume,
                            $this->ship->available_cargo_capacity
                        )
                    );
                }
                dump("{$this->ship->symbol} is fully loaded, flying to construction site");
                $this->flyToLocation($this->constructionSite->waypointSymbol);

                return;
            }
            $this->flyToLocation($this->supplyTradeOpportunity->waypoint_symbol);

            return;
        }

        if ($this->ship->waypoint_symbol === $this->constructionSite->waypointSymbol) {
            dump("{$this->ship->symbol} is empty, flying to supply route");
            $this->flyToLocation($this->supplyTradeOpportunity->waypoint_symbol);

            return;
        }

        if ($this->ship->waypoint_symbol !== $this->constructionSite->waypointSymbol) {
            dump("{$this->ship->symbol} is loaded, flying to construction site");
            $this->flyToLocation($this->constructionSite->waypointSymbol);

            return;
        }

        dump("{$this->ship->symbol} did nothing.");
    }

    private function initSupplyTradeOpportunity(): void
    {
        $this->supplyTradeOpportunity = $this->constructionSite
            ->constructionMaterial
            ->filter(
                fn (ConstructionMaterialData $constructionMaterialData) => $constructionMaterialData->unitsFulfilled < $constructionMaterialData->unitsRequired
            )
            ->map(
                fn (ConstructionMaterialData $constructionMaterialData) => TradeOpportunity::exports()
                    ->bySymbol($constructionMaterialData->tradeSymbol)
                    ->whereNotIn('supply', [SupplyLevels::SCARCE, SupplyLevels::LIMITED])
                    ->get()
            )
            ->flatten(1)
            ->sortBy('sell_price')
            ->first();
    }
}
