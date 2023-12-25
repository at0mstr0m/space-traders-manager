<?php

namespace App\Jobs;

use App\Actions\UpdateOrRemovePotentialTradeRoutesAction;
use App\Enums\TradeSymbols;
use App\Helpers\SpaceTraders;
use App\Models\PotentialTradeRoute;
use App\Models\Ship;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Bus\PendingDispatch;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ServeTradeRoute implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    private SpaceTraders $api;

    /**
     * Create a new job instance.
     */
    public function __construct(
        private string $shipSymbol,
        private string $origin,
        private string $destination,
        private TradeSymbols $tradedGood,
        private ?Ship $ship = null,
    ) {
        $this->api = app(SpaceTraders::class);
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->initShip();

        dump("{$this->ship->symbol} serving trade route {$this->origin} -> {$this->destination} with {$this->tradedGood->value}");

        if ($this->ship->is_in_transit || $this->ship->cooldown) {
            dump("{$this->ship->symbol} is in transit or on cooldown");
            $this->selfDispatch()->delay($this->ship->cooldown);

            return;
        }

        if ($this->ship->cargo_is_empty) {
            dump("{$this->ship->symbol} cargo is empty");
            if ($this->ship->waypoint_symbol === $this->origin) {
                UpdateOrRemovePotentialTradeRoutesAction::run();
                /** @var PotentialTradeRoute */
                $tradeRoute = PotentialTradeRoute::firstWhere([
                    'trade_symbol' => $this->tradedGood->value,
                    'origin' => $this->origin,
                    'destination' => $this->destination,
                ]);
                if (!$tradeRoute) {
                    dump("{$this->ship->symbol} trade route does not exist anymore");

                    return;
                }

                if ($tradeRoute->profit <= 2) {
                    dump("{$this->ship->symbol} trade route is not profitable enough");

                    return;
                }

                dump("{$this->ship->symbol} purchase cargo {$this->tradedGood->value}");
                $this->ship->purchaseCargo(
                    $this->tradedGood,
                    $this->ship->cargo_capacity > $tradeRoute->trade_volume_at_origin
                        ? $tradeRoute->trade_volume_at_origin
                        : $this->ship->cargo_capacity
                );
                dump("{$this->ship->symbol} fly to {$this->destination}");
                $this->flyToLocation($this->destination);

                return;
            }
            dump("{$this->ship->symbol} fly to {$this->origin}");
            $this->flyToLocation($this->origin);

            return;
        } else {
            dump("{$this->ship->symbol} cargo is not empty");
            if ($this->ship->waypoint_symbol === $this->destination) {
                dump("{$this->ship->symbol} sell cargo {$this->tradedGood->value}");
                $this->ship->sellCargo($this->tradedGood);
                dump("{$this->ship->symbol} fly to {$this->origin}");
                $this->flyToLocation($this->origin);

                return;
            }
            dump("{$this->ship->symbol} fly to {$this->destination}");
            $this->flyToLocation($this->destination);

            return;
        }

        dump('did not match any conditions');
    }

    private function initShip(): void
    {
        $this->ship ??= Ship::findBySymbol($this->shipSymbol);
        if (!$this->ship) {
            throw new \Exception("Ship not found: {$this->shipSymbol}");
        }
        $this->ship = $this->ship->refetch();
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

    private function selfDispatch(): PendingDispatch
    {
        return static::dispatch(
            $this->shipSymbol,
            $this->origin,
            $this->destination,
            $this->tradedGood,
            $this->ship,
        );
    }
}
