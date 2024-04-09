<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Actions\UpdateContractAction;
use App\Data\ContractData;
use App\Helpers\SpaceTraders;
use App\Models\Agent;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class UpdateContracts implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

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
        $this->api
            ->listContracts(all: true)
            ->each(
                fn (ContractData $contractData) => UpdateContractAction::run($contractData, $this->agent)
            );
    }
}
