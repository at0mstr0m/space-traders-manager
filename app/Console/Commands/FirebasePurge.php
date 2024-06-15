<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\Firebase;
use Illuminate\Console\Command;

class FirebasePurge extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'firebase-purge';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete all data from Firebase.';

    private string $confirmMessage = 'Do you really want to delete all data stored in the Firebase Realtime Database?';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        /** @var bool */
        $noInteraction = $this->option('no-interaction');
        /** @var Firebase */
        $firebase = app(Firebase::class);

        if (!$noInteraction && !$this->components->confirm($this->confirmMessage)) {
            $this->components->warn('Aborted.');

            return;
        }

        $this->components->info('Deleting data from Firebase...');
        $firebase->deleteAll();
        $this->components->info('Data deleted from Firebase.');
    }
}
