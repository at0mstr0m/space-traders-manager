<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Actions\Firebase\DownloadRelationsPotentialTradeRoutesToShipsAction;
use App\Actions\Firebase\DownloadTasksAction;
use App\Actions\Firebase\DownloadTaskShipRelationsAction;
use App\Actions\PurgeSystemsAction;
use App\Jobs\Firebase\DownloadSystemsJob;
use Illuminate\Console\Command;

class FirebaseDownload extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'firebase:download {--purge} {--systems}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Download data from Firebase.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        /** @var bool */
        $purge = $this->option('purge');
        $loadSystems = $this->option('systems');

        $this->components->info('Downloading Tasks from Firebase...');
        DownloadTasksAction::run($purge);
        $this->components->info('Tasks downloaded from Firebase.');

        $this->components->info('Downloading TaskShipRelations from Firebase...');
        DownloadTaskShipRelationsAction::run($purge);
        $this->components->info('TaskShipRelations downloaded from Firebase.');

        $this->components->info('Downloading Relations of PotentialTradeRoutes to Ships from Firebase...');
        DownloadRelationsPotentialTradeRoutesToShipsAction::run($purge);
        $this->components->info('Relations of PotentialTradeRoutes to Ships downloaded from Firebase.');

        $this->components->info('Done downloading data from Firebase.');

        if ($loadSystems) {
            PurgeSystemsAction::runIf($purge);
            $this->components->info('Triggering Systems Download from Firebase...');
            DownloadSystemsJob::dispatch();
        }
    }
}
