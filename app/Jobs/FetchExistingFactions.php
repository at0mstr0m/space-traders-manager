<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Faction;
use Illuminate\Support\Arr;
use App\Helpers\SpaceTraders;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class FetchExistingFactions implements ShouldQueue
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
        $this->api->listFactions(all: true)->each(function ($faction) {
            if (Faction::where('symbol', $faction['symbol'])->exists()) {
                return;
            }

            $attributes = Arr::snakeKeys(Arr::except($faction, ['traits']));
            Faction::new($attributes)->save();
        });
    }
}
