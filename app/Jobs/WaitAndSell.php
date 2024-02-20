<?php

namespace App\Jobs;

use App\Models\Cargo;
use App\Models\TradeOpportunity;
use Illuminate\Contracts\Queue\ShouldBeUniqueUntilProcessing;
use Illuminate\Support\Collection;

class WaitAndSell extends ShipJob implements ShouldBeUniqueUntilProcessing
{
    private array $closestTradeOpportunity = [];

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
            dump("{$this->ship->symbol} no longer has this task");
            return;
        }
        $waitingLocation = $this->ship->task->payload['waiting_location'];
        dump("{$this->ship->symbol} current location: {$currentLocation}");

        if ($this->ship->cargo_is_empty) {
            dump('cargo is empty');
            if ($currentLocation !== $waitingLocation) {
                dump('nothing to sell, fly to waiting location');
                $this->flyToLocation($waitingLocation);
            }

            return;
        }

        $markets = TradeOpportunity::randomMarketplacesForCargos($this->ship)
            ->pipe(function (Collection $marketData) {
                $this->ship
                    ->cargos()
                    ->whereNotIn('symbol', $marketData->keys()->all())
                    ->get()
                    ->each(fn (Cargo $cargo) => $this->ship->jettisonCargo($cargo->symbol));

                return $marketData;
            })
            ->sortBy('distance');

        /// todo: handle if is null
        $this->closestTradeOpportunity = $markets->first();

        if (!$this->closestTradeOpportunity) {
            dump('no trade opportunities');
            if ($currentLocation !== $waitingLocation) {
                dump('no trade opportunities, fly to waiting location');
                $this->flyToLocation($waitingLocation);
            }

            return;
        }

        dump($markets);
        dump($this->closestTradeOpportunity);

        if ($markets->isEmpty()) {
            dump('no markets, fly to waiting location');
            $this->flyToLocation($waitingLocation);

            return;
        }

        $waypointSymbol = $this->closestTradeOpportunity['waypoint_symbol'];

        // fly to market
        if ($currentLocation !== $waypointSymbol) {
            dump("fly to market at {$waypointSymbol}");
            $this->flyToLocation($waypointSymbol);

            return;
        }

        // sell all cargos that can be sold at this market
        dump('sell cargo');
        $markets->filter(
            fn (array $market) => $market['waypoint_symbol'] === $currentLocation
        )->each(
            function (array $market) {
                dump("selling cargo {$market['symbol']->value} at {$market['waypoint_symbol']}");

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
            dump('markets empty, fly to waiting location');
            $this->flyToLocation($waitingLocation);
        } else {
            dump('markets still not empty');
            $this->flyToLocation($markets->first()['waypoint_symbol']);

            return;
        }

        dump('done');
    }
}
