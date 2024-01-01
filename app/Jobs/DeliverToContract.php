<?php

namespace App\Jobs;

use App\Enums\TradeSymbols;
use App\Models\Delivery;
use App\Models\Ship;

class DeliverToContract extends ShipJob
{
    public function __construct(
        private string $shipSymbol,
        private string $waitingLocation,
        private Delivery $delivery,
        private TradeSymbols $tradedGood,
        private ?Ship $ship = null,
    ) {}

    /**
     * Execute the job.
     */
    protected function handleShip(): void
    {
        if ($this->delivery->units_to_be_delivered > $this->ship->cargo_capacity && !$this->ship->is_fully_loaded) {
            dump("{$this->ship->symbol} is not fully loaded.");

            return;
        }
        if ($this->ship->cargo_units < $this->delivery->units_to_be_delivered) {
            dump("{$this->ship->symbol} is not loaded with enough cargo.");

            return;
        }

        if ($this->delivery->refresh()->is_done) {
            dump("{$this->ship->symbol} delivery is already done.");

            return;
        }

        $contract = $this->delivery->contract->refetch();
        if ($contract) {
            dump("{$this->ship->symbol} contract is already fulfilled.");

            return;
        }

        if (!$this->ship->isLoadedWith($this->tradedGood)) {
            dump("{$this->ship->symbol} is not loaded with {$this->tradedGood->value}.");

            return;
        }

        if ($this->ship->waypoint_symbol !== $this->delivery->destination_symbol) {
            dump("{$this->ship->symbol} is not at the delivery destination.");
            $this->flyToLocation($this->delivery->destination_symbol);

            return;
        }

        dump("{$this->ship->symbol} is at the delivery destination.");

        $this->ship->deliverCargoToContract(
            $contract->identification,
            $this->tradedGood,
            $this->delivery->units_to_be_delivered < $this->ship->cargo_capacity
                ? $this->delivery->units_to_be_delivered
                : $this->ship->cargo_capacity
        );

        dump("{$this->ship->symbol} delivered {$this->tradedGood->value} to contract {$contract->identification}.");

        // todo: add automatic contract fulfillment
        // if ($contract->refresh()->is_done) {
        //     dump("{$this->ship->symbol} delivery is done.");
        //     $contract->fulfill();
        // }

        $this->flyToLocation($this->waitingLocation);

        dump('done');
    }
}
