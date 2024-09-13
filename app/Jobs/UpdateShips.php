<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Actions\UpdateShipAction;
use App\Data\ShipData;
use App\Helpers\SpaceTraders;
use App\Models\Agent;
use App\Models\Faction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

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
    public function __construct(
        private ?Agent $agent = null,
        private null|array|Arrayable|Collection $pages = null,
    ) {
        if ($pages) {
            $this->pages = collect($pages);
        }

        $this->agent ??= Agent::first();
        $this->api = app(SpaceTraders::class);
        $this->agentFaction = Faction::findBySymbol($this->agent->starting_faction);
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        /** @var Collection */
        $shipData = $this->pages
            ? $this->pages
                ->map(fn (int $page) => $this->api->listShips(page: $page, all: true))
                ->flatten(1)
            : $this->api->listShips(all: true);

        $shipData->each(function (ShipData $data) {
            $shipFaction = Faction::find($data->factionId);
            if ($this->agentFaction->isNot($shipFaction)) {
                throw new \Exception('Ship faction does not match agent faction');
            }
            UpdateShipAction::run($data, $this->agent);
        });
    }
}
