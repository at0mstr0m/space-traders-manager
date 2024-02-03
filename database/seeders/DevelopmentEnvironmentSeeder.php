<?php

namespace Database\Seeders;

use App\Jobs\UpdateContracts;
use Illuminate\Database\Seeder;
use App\Actions\RelateAgentToUser;
use App\Actions\UpdateWaypointsAction;
use App\Jobs\UpdateExistingFactions;
use App\Jobs\UpdateShips;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

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
    }
}
