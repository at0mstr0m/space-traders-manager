<?php

namespace App\Jobs;

use App\Enums\TradeSymbols;
use App\Helpers\SpaceTraders;
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
        private string $from,
        private string $to,
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

        dump("{$this->ship->symbol} serving trade route {$this->from} -> {$this->to} with {$this->tradedGood->value}");

        if ($this->ship->is_in_transit || $this->ship->cooldown) {
            dump("{$this->ship->symbol} is in transit or on cooldown");
            $this->selfDispatch()->delay($this->ship->cooldown);
        }

        if ($this->ship->cargo_is_empty) {
            dump("{$this->ship->symbol} cargo is empty");
            if ($this->ship->waypoint_symbol === $this->from) {
                dump("{$this->ship->symbol} purchase cargo {$this->tradedGood->value}");
                $this->ship->purchaseCargo($this->tradedGood);
                dump("{$this->ship->symbol} fly to {$this->to}");
                $this->flyToLocation($this->to);
            }
            dump("{$this->ship->symbol} fly to {$this->from}");
            $this->flyToLocation($this->from);

            return;
        }

        if ($this->ship->is_fully_loaded) {
            dump("{$this->ship->symbol} cargo is full");
            if ($this->ship->waypoint_symbol === $this->to) {
                dump("{$this->ship->symbol} sell cargo {$this->tradedGood->value}");
                $this->ship->sellCargo($this->tradedGood);
                dump("{$this->ship->symbol} fly to {$this->from}");
                $this->flyToLocation($this->from);

                return;
            }
            dump("{$this->ship->symbol} fly to {$this->to}");
            $this->flyToLocation($this->to);

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
            $this->from,
            $this->to,
            $this->tradedGood,
            $this->ship,
        );
    }
}
