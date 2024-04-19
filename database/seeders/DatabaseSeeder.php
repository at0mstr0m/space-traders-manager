<?php

declare(strict_types=1);

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Actions\RelateAgentToUser;
use App\Actions\UpdateOrRemoveTradeOpportunitiesAction;
use App\Actions\UpdateSystemsAction;
use App\Actions\UpdateWaypointsAction;
use App\Jobs\UpdateContracts;
use App\Jobs\UpdateExistingFactions;
use App\Jobs\UpdateShips;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
        ]);

        UpdateExistingFactions::dispatchSync();
        RelateAgentToUser::run();
        UpdateContracts::dispatchSync();
        UpdateShips::dispatchSync();
        UpdateSystemsAction::run();
        UpdateWaypointsAction::run();
        UpdateOrRemoveTradeOpportunitiesAction::run();
    }
}
