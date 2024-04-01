<?php

declare(strict_types=1);

namespace App\Actions;

use App\Data\ContractData;
use App\Data\DeliveryData;
use App\Models\Agent;
use App\Models\Contract;
use Lorisleiva\Actions\Concerns\AsAction;

class UpdateContractAction
{
    use AsAction;

    public function handle(ContractData $contractData, Agent $agent): Contract
    {
        // update contract
        $contract = $agent->contracts()->updateOrCreate(
            ['identification' => $contractData->identification],
            [
                'faction_symbol' => $contractData->factionSymbol,
                'type' => $contractData->type,
                'accepted' => $contractData->accepted,
                'fulfilled' => $contractData->fulfilled,
                'deadline' => $contractData->deadline,
                'deadline_to_accept' => $contractData->deadlineToAccept,
                'payment_on_accepted' => $contractData->paymentOnAccepted,
                'payment_on_fulfilled' => $contractData->paymentOnFulfilled,
            ]
        );

        // update its deliveries
        $contractData->deliveries
            ->each(
                fn (DeliveryData $deliveryData) => $contract->deliveries()->updateOrCreate(
                    [
                        'trade_symbol' => $deliveryData->tradeSymbol,
                        'destination_symbol' => $deliveryData->destinationSymbol,
                        'units_required' => $deliveryData->unitsRequired,
                    ],
                    [
                        'units_fulfilled' => $deliveryData->unitsFulfilled,
                    ]
                )
            );
        $contract->save();

        return $contract;
    }
}
