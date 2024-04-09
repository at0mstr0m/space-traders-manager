<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Data\FactionData;
use App\Data\FactionTraitData;
use App\Helpers\SpaceTraders;
use App\Models\Faction;
use App\Models\FactionTrait;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class UpdateExistingFactions implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    private SpaceTraders $api;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        $this->api = app(SpaceTraders::class);
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->api->listFactions(all: true)->each(function (FactionData $factionData) {
            $faction = Faction::updateOrCreate(
                ['symbol' => $factionData->symbol],
                [
                    'name' => $factionData->name,
                    'description' => $factionData->description,
                    'headquarters' => $factionData->headquarters,
                    'is_recruiting' => $factionData->isRecruiting,
                ]
            );

            $factionData->traits->each(function (FactionTraitData $factionTraitData) use ($faction) {
                $factionTrait = FactionTrait::updateOrCreate(
                    ['symbol' => $factionTraitData->symbol],
                    [
                        'name' => $factionTraitData->name,
                        'description' => $factionTraitData->description,
                    ]
                );
                $faction->traits()->syncWithoutDetaching($factionTrait);
            });
        });
    }
}
