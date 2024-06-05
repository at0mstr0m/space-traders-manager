<?php

namespace App\Jobs;

use App\Enums\TradeSymbols;
use App\Models\Delivery;
use App\Models\Ship;

class DeliverToContract extends ShipJob
{
    public function __construct(
        protected string $shipSymbol,
        private string $waitingLocation,
        private Delivery $delivery,
        private TradeSymbols $tradedGood,
        protected ?Ship $ship = null,
    ) {}

    /**
     * Execute the job.
     */
    protected function handleShip(): void
    {
        if ($this->delivery->units_to_be_delivered > $this->ship->cargo_capacity && !$this->ship->is_fully_loaded) {
            $this->log('is not fully loaded.');

            return;
        }
        if ($this->ship->cargo_units < $this->delivery->units_to_be_delivered) {
            $this->log('is not loaded with enough cargo.');

            return;
        }

        if ($this->delivery->refresh()->is_done) {
            $this->log('delivery is already done.');

            return;
        }

        $contract = $this->delivery->contract->refetch();
        if ($contract) {
            $this->log('contract is already fulfilled.');

            return;
        }

        if (!$this->ship->isLoadedWith($this->tradedGood)) {
            $this->log("is not loaded with {$this->tradedGood->value}.");

            return;
        }

        if ($this->ship->waypoint_symbol !== $this->delivery->destination_symbol) {
            $this->log('is not at the delivery destination.');
            $this->flyToLocation($this->delivery->destination_symbol);

            return;
        }

        $this->log('is at the delivery destination.');

        $this->ship->deliverCargoToContract(
            $contract->identification,
            $this->tradedGood,
            $this->delivery->units_to_be_delivered < $this->ship->cargo_capacity
                ? $this->delivery->units_to_be_delivered
                : $this->ship->cargo_capacity
        );

        $this->log("delivered {$this->tradedGood->value} to contract {$contract->identification}.");

        // todo: add automatic contract fulfillment
        // if ($contract->refresh()->is_done) {
        //     $this->log("delivery is done.");
        //     $contract->fulfill();
        // }

        $this->flyToLocation($this->waitingLocation);

        $this->log('done');
    }
}
