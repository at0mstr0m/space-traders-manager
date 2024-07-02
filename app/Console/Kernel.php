<?php

namespace App\Console;

use App\Actions\TriggerTasks;
use App\Actions\UpdateOrRemoveTradeOpportunitiesAction;
use App\Jobs\UpdateContracts;
use App\Jobs\UpdateExistingFactions;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        $schedule->job(new UpdateExistingFactions())->daily();
        $schedule->job(new UpdateContracts())->everyTenMinutes();
        $schedule->job(UpdateOrRemoveTradeOpportunitiesAction::makeUniqueJob())->everyTwoMinutes();
        $schedule->job(TriggerTasks::makeUniqueJob())->hourly();
        $schedule->command('model:prune')->everyMinute();
        $schedule->command('horizon:snapshot')->everyFiveMinutes();
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
