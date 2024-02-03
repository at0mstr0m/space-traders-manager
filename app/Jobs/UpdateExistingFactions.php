<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Faction;
use App\Data\FactionData;
use App\Models\FactionTrait;
use App\Helpers\SpaceTraders;
use Illuminate\Bus\Queueable;
use App\Data\FactionTraitData;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

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
