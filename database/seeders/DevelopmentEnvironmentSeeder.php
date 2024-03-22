<?php

namespace Database\Seeders;

use App\Actions\RelateAgentToUser;
use App\Actions\UpdateOrRemoveTradeOpportunitiesAction;
use App\Actions\UpdateWaypointsAction;
use App\Jobs\UpdateContracts;
use App\Jobs\UpdateExistingFactions;
use App\Jobs\UpdateShips;
use Illuminate\Database\Seeder;

class DevelopmentEnvironmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        UpdateExistingFactions::dispatchSync();
        RelateAgentToUser::run();
        UpdateContracts::dispatchSync();
        UpdateShips::dispatchSync();
        UpdateWaypointsAction::run();
        UpdateOrRemoveTradeOpportunitiesAction::run();
    }
}
