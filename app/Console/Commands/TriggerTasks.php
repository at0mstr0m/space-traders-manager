<?php

namespace App\Console\Commands;

use App\Actions\TriggerTasks as TriggerTasksAction;
use Illuminate\Console\Command;

class TriggerTasks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'trigger-tasks';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Triggers all tasks.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->components->info('Triggering tasks...');
        TriggerTasksAction::run();
    }
}
