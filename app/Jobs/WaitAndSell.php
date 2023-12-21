<?php

namespace App\Jobs;

use App\Data\MarketData;
use App\Helpers\SpaceTraders;
use App\Models\Ship;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Bus\PendingDispatch;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class WaitAndSell implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    private SpaceTraders $api;

    /**
     * Create a new job instance.
     *
     * @template TTradeSymbolString string
     * @template TMarketPlaceWaypointSymbolString string
     *
     * @param Collection|null $markets <TTradeSymbolString, TMarketPlaceWaypointSymbolString> $markets
     */
    public function __construct(
        private string $shipSymbol,
        private ?string $waitingLocation = null,
        private ?Ship $ship = null,
        private ?Collection $markets = null,
    ) {
        $this->api = app(SpaceTraders::class);
        $this->markets ??= collect();
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->initShip();

        if ($this->ship->is_in_transit || $this->ship->cooldown) {
            $this->selfDispatch()->delay($this->ship->cooldown);
        }

        $currentLocation = $this->ship->waypoint_symbol;
        dump("current location: {$currentLocation}");

        if ($this->ship->cargo_is_empty) {
            dump('cargo is empty');
            if ($currentLocation !== $this->waitingLocation) {
                dump('nothing to sell, fly to waiting location');
                $this->flyToLocation($this->waitingLocation);
            }

            return;
        }

        $this->markets = $this->ship
            ->getMarketplacesForCargos()
            ->each(function (?MarketData $marketData, string $tradeSymbol) {
                // no place in System to sell => move cargo to COMMAND Ship waiting
                if (!$marketData) {
                    dump('no place in System to sell');
                    $this->ship->jettisonCargo($tradeSymbol);
                }
            })->filter()    // remove items without Market found in System
            ->map(fn (MarketData $marketData) => $marketData->symbol)
            ->sort();

        dump($this->markets);

        if ($this->markets->isEmpty()) {
            dump('no markets, fly to waiting location');
            $this->flyToLocation($this->waitingLocation);

            return;
        }

        $marketSymbol = $this->markets->first();
        // fly to first market
        if ($currentLocation !== $marketSymbol) {
            dump("fly to market at {$marketSymbol}");
            $this->flyToLocation($marketSymbol);

            return;
        }

        // sell all cargos that can be sold at this market
        dump('sell cargo');
        $this->markets
            ->filter(
                fn (string $marketSymbol) => $marketSymbol === $currentLocation
            )->each(
                function (string $marketSymbol, string $tradeSymbol) {
                    dump("selling cargo {$tradeSymbol} at {$marketSymbol}");
                    $this->ship->sellCargo($tradeSymbol);
                }
            );

        // remove sold items from market list
        $this->markets = $this->markets
            ->filter(
                fn (string $marketSymbol) => $marketSymbol !== $currentLocation
            );

        if ($this->markets->isEmpty()) {
            $this->flyToLocation($this->waitingLocation);
        } else {
            dump('markets still not empty');
            $this->selfDispatch();

            return;
        }

        dump('done');
    }

    private function initShip(): void
    {
        $this->ship ??= Ship::findBySymbol($this->shipSymbol);
        if (!$this->ship) {
            throw new \Exception("Ship not found: {$this->shipSymbol}");
        }
        $this->ship = $this->ship->refetch();
    }

    private function selfDispatch(): PendingDispatch
    {
        return static::dispatch(
            $this->shipSymbol,
            $this->waitingLocation,
            $this->ship,
            $this->markets,
        );
    }

    private function flyToLocation(string $waypointSymbol): void
    {
        dump("fly to {$waypointSymbol}");
        $cooldown = $this->ship
            ->refuel()
            ->navigateTo($waypointSymbol)
            ->cooldown;
        $this->selfDispatch()->delay($cooldown);
    }
}
