<?php

namespace App\Console;

use App\Actions\UpdateOrRemoveTradeOpportunitiesAction;
use App\Enums\TaskTypes;
use App\Jobs\ServeRandomTradeRoute;
use App\Jobs\UpdateContracts;
use App\Jobs\UpdateExistingFactions;
use App\Models\PotentialTradeRoute;
use App\Models\Ship;
use App\Models\Task;
use App\Models\User;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Cache;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        $schedule->job(new UpdateExistingFactions())->daily();
        $schedule->call(function () {
            // Cache::tags([PotentialTradeRoute::CACHE_TAG])->flush();
            Task::where('type', TaskTypes::SERVE_TRADE_ROUTE)
                ->each(
                    fn (Task $task) => $task->ships->each(
                        fn (Ship $ship) => ServeRandomTradeRoute::dispatch($ship->symbol)
                    )
                );
        })->everySixHours();
        $schedule->job(new UpdateContracts(User::find(1)->agent))->everyTenMinutes();
        $schedule->job(UpdateOrRemoveTradeOpportunitiesAction::makeUniqueJob())->everyTwoMinutes();
        $schedule->command('model:prune')->everyMinute();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
