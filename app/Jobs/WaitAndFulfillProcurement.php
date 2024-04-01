<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Enums\ContractTypes;
use App\Enums\TaskTypes;
use App\Models\Contract;
use App\Models\Delivery;
use App\Models\Task;
use App\Models\TradeOpportunity;

class WaitAndFulfillProcurement extends ShipJob
{
    // todo: associate & dissociate ship with contract
    private ?Contract $currentContract = null;

    private ?string $waitingLocation = null;

    protected function handleShip(): void
    {
        $this->initContract();

        if (!$this->currentContract) {
            dump('No procurement contract to fulfill, must negotiate new contract at HQ');
            if ($this->ship->waypoint_symbol !== ($headquarters = $this->ship->agent->headquarters)) {
                $this->flyToLocation($headquarters);

                dump("fly to headquarters at {$headquarters}");

                return;
            }

            dump("Negotiating new Contract at HQ {$headquarters}");
            $this->currentContract = $this->ship->negotiateContract();
        }

        // dump('Evaluate closest extraction location');

        // $this->waitingLocation = data_get(
        //     Task::where('type', TaskTypes::COLLECTIVE_MINING)
        //         ->pluck('payload')
        //         ->pluck('extraction_location')
        //         ->map(fn (string $waypointSymbol) => [
        //             'waypointSymbol' => $waypointSymbol,
        //             'distance' => $this->ship->distanceTo($waypointSymbol),
        //         ])
        //         ->sortBy('distance')
        //         ->first(),
        //     'waypointSymbol'
        // );

        // if (!$this->waitingLocation) {
        //     dump('No extraction location available.');

        //     return;
        // }

        // if ($this->ship->waypoint_symbol !== $this->waitingLocation) {
        //     $this->flyToLocation($this->waitingLocation);

        //     dump("fly to extraction location at {$this->waitingLocation}");

        //     return;
        // }

        /** @var Delivery $currentDelivery */
        $currentDelivery = $this->currentContract->deliveries()->onlyUnfulfilled()->first();

        if ($this->ship->cargo_is_empty) {
            $purchaseLocation = TradeOpportunity::bySymbol($currentDelivery->trade_symbol)
                ->orderBy('purchase_price')
                ->first()
                ?->waypoint_symbol;

            if (!$purchaseLocation) {
                throw new \Exception("No purchase location for {$currentDelivery->trade_symbol->value} available");
            }

            if ($this->ship->waypoint_symbol !== $purchaseLocation) {
                $this->flyToLocation($purchaseLocation);

                dump("fly to {$purchaseLocation} to purchase {$currentDelivery->trade_symbol->value}");

                return;
            }

            dump("{$this->ship->symbol} purchasing {$currentDelivery->trade_symbol->value}");
            $this->ship->purchaseCargo(
                $currentDelivery->trade_symbol,
                min($currentDelivery->units_to_be_delivered, $this->ship->available_cargo_capacity)
            );

            $this->flyToLocation($currentDelivery->destination_symbol);

            return;
        }

        if ($this->ship->waypoint_symbol === $currentDelivery->destination_symbol) {
            dump("{$this->ship->symbol} delivering {$currentDelivery->trade_symbol->value}");
            $this->ship->deliverCargoToContract(
                $this->currentContract->identification,
                $currentDelivery->trade_symbol,
                $this->ship->cargos()->firstWhere('symbol', $currentDelivery->trade_symbol)->units
            );

            dump('fulfill contract if no more deliveries are necessary');

            if (
                $this->currentContract
                    ->refresh()
                    ->deliveries()
                    ->onlyUnfulfilled()
                    ->doesntExist()
            ) {
                $this->currentContract->fulfill();
            }

            dump('done with contract, self dispatching');

            $this->selfDispatch();

            return;
        }

        dump('did nothing');
    }

    private function initContract(): void
    {
        if (
            $this->ship->agent->contracts()->where([
                ['type', '=', ContractTypes::PROCUREMENT],
                ['accepted', '=', false],
                ['fulfilled', '=', false],
                ['deadline', '>', now()],
                ['deadline_to_accept', '>', now()],
            ])->exists()
        ) {
            $this->currentContract = $this->ship
                ->agent
                ->contracts()
                ->firstWhere([
                    ['type', '=', ContractTypes::PROCUREMENT],
                    ['accepted', '=', false],
                    ['fulfilled', '=', false],
                    ['deadline', '>', now()],
                    ['deadline_to_accept', '>', now()],
                ])
                ->accept();

            dump("accepting procurement contract {$this->currentContract->id}");
        } elseif (
            $this->ship->agent->contracts()->where([
                ['type', '=', ContractTypes::PROCUREMENT],
                ['accepted', '=', true],
                ['fulfilled', '=', false],
                ['deadline', '>', now()],
                ['deadline_to_accept', '>', now()],
            ])->exists()
        ) {
            $this->currentContract = $this->ship->agent->contracts()->where([
                ['type', '=', ContractTypes::PROCUREMENT],
                ['accepted', '=', true],
                ['fulfilled', '=', false],
                ['deadline', '>', now()],
                ['deadline_to_accept', '>', now()],
            ])->first();

            dump("Using already accepted procurement contract {$this->currentContract->id}");
        }
    }
}
