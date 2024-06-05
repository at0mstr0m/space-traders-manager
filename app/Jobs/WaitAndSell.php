<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Enums\SupplyLevels;
use App\Helpers\LocationHelper;
use App\Models\Cargo;
use App\Models\TradeOpportunity;
use Illuminate\Contracts\Queue\ShouldBeUniqueUntilProcessing;
use Illuminate\Support\Collection;

class WaitAndSell extends ShipJob implements ShouldBeUniqueUntilProcessing
{
    private ?array $closestTradeOpportunity = [];

    /**
     * Create a new job instance.
     */
    public function __construct(protected string $shipSymbol)
    {
        $this->constructorArguments = func_get_args();
    }

    /**
     * Get the unique ID for the job.
     */
    public function uniqueId(): string
    {
        return static::class . ':' . $this->shipSymbol;
    }

    /**
     * Execute the job.
     */
    protected function handleShip(): void
    {
        $this->initApi();
        $currentLocation = $this->ship->waypoint_symbol;
        if (!$this->ship->task?->payload) {
            $this->log("no longer has this task");

            return;
        }
        $waitingLocation = $this->ship->task->payload['waiting_location'];
        $this->log("current location: {$currentLocation}");

        if ($this->ship->cargo_is_empty) {
            $this->log('cargo is empty');
            if ($currentLocation !== $waitingLocation) {
                $this->log('nothing to sell, fly to waiting location');
                $this->flyToLocation($waitingLocation);
            }

            return;
        }

        $didJettison = false;
        $markets = TradeOpportunity::randomMarketplacesForCargos($this->ship)
            ->pipe(function (Collection $marketData) use (&$didJettison) {
                $this->ship
                    ->cargos()
                    ->whereNotIn('symbol', $marketData->keys()->all())
                    ->get()
                    ->each(function (Cargo $cargo) use ($marketData, &$didJettison) {
                        $exchanges = TradeOpportunity::exchanges()
                            ->bySymbol($cargo->symbol)
                            ->whereNotIn('supply', [SupplyLevels::ABUNDANT, SupplyLevels::HIGH])
                            ->get()
                            ->map(fn (TradeOpportunity $tradeOpportunity) => [
                                ...$tradeOpportunity->only([
                                    'symbol',
                                    'waypoint_symbol',
                                    'sell_price',
                                    'trade_volume',
                                ]),
                                'distance' => LocationHelper::distance(
                                    $this->ship->waypoint_symbol,
                                    $tradeOpportunity->waypoint_symbol
                                ),
                            ]);
                            // ->when(
                            //     $this->ship->fuel_capacity > 0,
                            //     fn (Collection $tradeOpportunities) => $tradeOpportunities->filter(
                            //         fn (array $tradeOpportunity) => $tradeOpportunity['distance'] <= $this->ship->fuel_capacity
                            //     )
                            // );

                        if ($exchanges->isEmpty()) {
                            $this->log("cannot even sell {$cargo->symbol->value} at exchange, jettison {$cargo->units} units");

                            $this->ship->jettisonCargo($cargo->symbol);
                            $didJettison = true;

                            return;
                        }

                        $this->log("keeping {$cargo->symbol->value} to sell at exchange");

                        $marketData->put($cargo->symbol->value, $exchanges->random());
                    });

                return $marketData;
            })
            ->sortBy('distance');

        if ($didJettison && $currentLocation === $waitingLocation) {
            $this->log('not fully loaded after jettisoning, keep waiting');

            return;
        }

        $this->closestTradeOpportunity = $markets->first();

        if (!$this->closestTradeOpportunity) {
            $this->log('no trade opportunities');
            if ($currentLocation !== $waitingLocation) {
                $this->log('no trade opportunities, fly to waiting location');
                $this->flyToLocation($waitingLocation);
            }

            return;
        }

        $this->log('Markets: :?', [(string) $markets]);
        $this->log('closestTradeOpportunity: :?', [json_encode($this->closestTradeOpportunity)]);

        if ($markets->isEmpty()) {
            $this->log('no markets, fly to waiting location');
            $this->flyToLocation($waitingLocation);

            return;
        }

        $waypointSymbol = $this->closestTradeOpportunity['waypoint_symbol'];

        // fly to market
        if ($currentLocation !== $waypointSymbol) {
            $this->log("fly to market at {$waypointSymbol}");
            $this->flyToLocation($waypointSymbol);

            return;
        }

        // sell all cargos that can be sold at this market
        $this->log('sell cargo');
        $markets->filter(
            fn (array $market) => $market['waypoint_symbol'] === $currentLocation
        )->each(
            function (array $market) {
                $this->log("selling cargo {$market['symbol']->value} at {$market['waypoint_symbol']}");

                while ($cargo = $this->ship->refresh()->cargos()->firstWhere('symbol', $market['symbol'])) {
                    $this->ship->sellCargo(
                        $market['symbol'],
                        min($market['trade_volume'], $cargo->units)
                    );
                }
            }
        );

        // remove sold items from market list
        $markets = $markets->reject(
            fn (array $market) => $market['waypoint_symbol'] === $currentLocation
        );

        if ($markets->isEmpty()) {
            $this->log('markets empty, fly to waiting location');
            $this->flyToLocation($waitingLocation);
        } else {
            $this->log('markets still not empty');
            $this->flyToLocation($markets->first()['waypoint_symbol']);

            return;
        }

        $this->log('done');
    }
}
