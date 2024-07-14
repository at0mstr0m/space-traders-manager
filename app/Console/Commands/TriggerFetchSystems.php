<?php

namespace App\Console\Commands;

use App\Jobs\FetchSystemsJob;
use Illuminate\Console\Command;

class TriggerFetchSystems extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dispatch-fetch-systems';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Triggers Job to fetch all Systems.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->components->info('Triggering Job to fetch all Systems ...');
        FetchSystemsJob::dispatch();
    }
}
