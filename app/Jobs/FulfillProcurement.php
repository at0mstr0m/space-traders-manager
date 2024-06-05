<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Enums\ContractTypes;
use App\Enums\TradeGoodTypes;
use App\Models\Contract;
use App\Models\Delivery;
use App\Models\TradeOpportunity;

class FulfillProcurement extends ShipJob
{
    // todo: associate & dissociate ship with contract
    private ?Contract $currentContract = null;

    protected function handleShip(): void
    {
        $this->initContract();

        if (!$this->currentContract) {
            $this->log('No procurement contract to fulfill, must negotiate new contract at HQ');
            if ($this->ship->waypoint_symbol !== ($headquarters = $this->ship->agent->headquarters)) {
                $this->flyToLocation($headquarters);

                $this->log("fly to headquarters at {$headquarters}");

                return;
            }

            $this->log("Negotiating new Contract at HQ {$headquarters}");
            $this->currentContract = $this->ship->negotiateContract();
        }

        /** @var Delivery $currentDelivery */
        $currentDelivery = $this->currentContract->deliveries()->onlyUnfulfilled()->first();

        if (!$currentDelivery) {
            $this->log('No more deliveries to fulfill, fulfilling contract, self dispatching');
            $this->currentContract->fulfill();
            $this->selfDispatch()->delay(1);

            return;
        }

        if ($this->ship->cargo_is_empty) {
            $tradeOpportunity = TradeOpportunity::bySymbol($currentDelivery->trade_symbol)
                ->whereIn('type', [TradeGoodTypes::EXPORT, TradeGoodTypes::EXCHANGE])
                ->orderBy('purchase_price')
                ->first();

            $purchaseLocation = $tradeOpportunity?->waypoint_symbol;

            if (!$purchaseLocation) {
                throw new \Exception("No purchase location for {$currentDelivery->trade_symbol->value} available");
            }

            if ($this->ship->waypoint_symbol !== $purchaseLocation) {
                $this->flyToLocation($purchaseLocation);

                $this->log("fly to {$purchaseLocation} to purchase {$currentDelivery->trade_symbol->value}");

                return;
            }

            $this->log("purchasing {$currentDelivery->trade_symbol->value}");
            $this->ship->purchaseCargo(
                $currentDelivery->trade_symbol,
                min(
                    $currentDelivery->units_to_be_delivered,
                    $this->ship->available_cargo_capacity,
                    $tradeOpportunity->trade_volume
                )
            );

            $this->flyToLocation($currentDelivery->destination_symbol);

            return;
        }

        if ($this->ship->waypoint_symbol === $currentDelivery->destination_symbol) {
            $this->log("delivering {$currentDelivery->trade_symbol->value}");
            $this->ship->deliverCargoToContract(
                $this->currentContract->identification,
                $currentDelivery->trade_symbol,
                $this->ship->cargos()->firstWhere('symbol', $currentDelivery->trade_symbol)->units
            );

            $this->log('fulfill contract if no more deliveries are necessary');

            if (
                $this->currentContract
                    ->refresh()
                    ->deliveries()
                    ->onlyUnfulfilled()
                    ->doesntExist()
            ) {
                $this->currentContract->fulfill();
            }

            $this->log('done with contract, self dispatching');
            $this->selfDispatch()->delay(1);

            return;
        }

        $this->log("is neither empty nor at delivery location, traveling to delivery destination at {$currentDelivery->destination_symbol}");

        $this->flyToLocation($currentDelivery->destination_symbol);
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

            $this->log("accepting procurement contract {$this->currentContract->id}");
        } elseif (
            $this->ship->agent->contracts()->where([
                ['type', '=', ContractTypes::PROCUREMENT],
                ['accepted', '=', true],
                ['fulfilled', '=', false],
                ['deadline', '>', now()],
            ])->exists()
        ) {
            $this->currentContract = $this->ship->agent->contracts()->where([
                ['type', '=', ContractTypes::PROCUREMENT],
                ['accepted', '=', true],
                ['fulfilled', '=', false],
                ['deadline', '>', now()],
            ])->first();

            $this->log("Using already accepted procurement contract {$this->currentContract->id}");
        }
    }
}
