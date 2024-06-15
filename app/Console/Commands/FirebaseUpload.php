<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\PotentialTradeRoute;
use App\Models\Ship;
use App\Models\Task;
use App\Services\Firebase;
use Illuminate\Console\Command;

class FirebaseUpload extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'firebase-upload {--purge}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Upload data to Firebase.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        /** @var bool */
        $purge = $this->option('purge');

        /** @var Firebase */
        $firebase = app(Firebase::class);

        if ($purge) {
            $this->components->info('Purging data from Firebase...');
            $firebase->deleteAll();
            $this->components->info('Data purged from Firebase.');
        }

        $this->components->info('Uploading Tasks to Firebase...');
        Task::all()->each(fn (Task $task) => $firebase->uploadTask($task));
        $this->components->info('Tasks uploaded to Firebase.');

        $this->components->info('Uploading TaskShipRelations to Firebase...');
        PotentialTradeRoute::all()
            ->each(
                fn (PotentialTradeRoute $route) => $firebase->uploadPotentialTradeRoute($route)
            );
        $this->components->info('TaskShipRelations uploaded to Firebase.');

        $this->components->info('Uploading Relations of PotentialTradeRoutes to Ships to Firebase...');
        Ship::all()->each(fn (Ship $ship) => $firebase->setShipTaskRelation($ship));
        $this->components->info('Relations of PotentialTradeRoutes to Ships uploaded to Firebase.');

        $this->components->info('Done uploading data to Firebase.');
    }
}
