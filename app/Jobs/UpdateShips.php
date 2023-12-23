<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Agent;
use App\Data\ShipData;
use App\Models\Faction;
use App\Helpers\SpaceTraders;
use Illuminate\Bus\Queueable;
use App\Actions\UpdateShipAction;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class UpdateShips implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    private SpaceTraders $api;
    private Faction $agentFaction;

    /**
     * Create a new job instance.
     */
    public function __construct(private ?Agent $agent = null)
    {
        $this->agent ??= Agent::first();
        $this->api = app(SpaceTraders::class);
        $this->agentFaction = Faction::findBySymbol($this->agent->starting_faction);
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // update ships
        $this->api->listShips(all:true)->each(function (ShipData $shipData) {
            $shipFaction = Faction::find($shipData->factionId);
            if ($this->agentFaction->isNot($shipFaction)) {
                throw new \Exception('Ship faction does not match agent faction');
            }
            UpdateShipAction::run($shipData, $this->agent);
        });
    }
}
