<?php

namespace App\Console;

use App\Models\User;
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
        // $schedule->command('inspire')->hourly();
        $schedule->job(new UpdateExistingFactions())->dailyAt('00:05');
        $schedule->job(new UpdateContracts(User::find(1)->agent))->everyTenMinutes();
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
