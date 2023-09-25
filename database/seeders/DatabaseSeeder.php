<?php

declare(strict_types=1);

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Jobs\UpdateContracts;
use Illuminate\Database\Seeder;
use App\Actions\RelateAgentToUser;
use Illuminate\Support\Facades\App;
use App\Jobs\UpdateExistingFactions;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        if (App::isLocal()) {
            $this->call([
                UserSeeder::class,
            ]);
            UpdateExistingFactions::dispatchSync();
            RelateAgentToUser::run();
            UpdateContracts::dispatchSync();
        }
    }
}
