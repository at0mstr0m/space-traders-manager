<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Actions\UpdateOrRemovePotentialTradeRoutesAction;
use App\Enums\TradeSymbols;
use App\Models\PotentialTradeRoute;
use App\Models\Ship;

class ServeTradeRoute extends ShipJob
{
    /**
     * Create a new job instance.
     */
    public function __construct(
        protected string $shipSymbol,
        private string $origin,
        private string $destination,
        private TradeSymbols $tradedGood,
        protected ?Ship $ship = null,
    ) {
        $this->constructorParams = func_get_args();
    }

    /**
     * Execute the job.
     */
    public function handleShip(): void
    {
        dump("{$this->ship->symbol} serving trade route {$this->origin} -> {$this->destination} with {$this->tradedGood->value}");

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

                if ($tradeRoute->profit <= 2 && $tradeRoute->profit !== 0) {
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
        }
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

        dump('did not match any conditions');
    }
}
