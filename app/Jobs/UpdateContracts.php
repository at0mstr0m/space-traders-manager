<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Agent;
use App\Data\ContractData;
use App\Data\DeliveryData;
use App\Helpers\SpaceTraders;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldBeUnique;

class UpdateContracts implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private SpaceTraders $api;

    /**
     * Create a new job instance.
     */
    public function __construct(private ?Agent $agent = null)
    {
        $this->agent ??= Agent::first();
        $this->api = app(SpaceTraders::class);
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->api->listContracts(all: true)->each(function (ContractData $contractData) {
            // update contract
            $contract = $this->agent->contracts()->updateOrCreate(
                ['identification' => $contractData->identification],
                [
                    'identification' => $contractData->identification,
                    'faction_symbol' => $contractData->factionSymbol,
                    'type' => $contractData->type,
                    'fulfilled' => $contractData->fulfilled,
                    'deadline' => $contractData->deadline,
                    'deadline_to_accept' => $contractData->deadlineToAccept,
                    'payment_on_accepted' => $contractData->paymentOnAccepted,
                    'payment_on_fulfilled' => $contractData->paymentOnFulfilled,
                ]
            );

            // update its deliveries
            $contractData->deliveries->each(function (DeliveryData $deliveryData) use ($contract) {
                $contract->deliveries()->updateOrCreate(
                    [
                        'trade_symbol' => $deliveryData->tradeSymbol,
                        'destination_symbol' => $deliveryData->destinationSymbol,
                        'units_required' => $deliveryData->unitsRequired,
                    ],
                    [
                        'trade_symbol' => $deliveryData->tradeSymbol,
                        'destination_symbol' => $deliveryData->destinationSymbol,
                        'units_required' => $deliveryData->unitsRequired,
                        'units_fulfilled' => $deliveryData->unitsFulfilled,
                    ]
                );
            });
        });
    }
}
