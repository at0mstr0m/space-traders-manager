<?php

namespace App\Jobs;

use App\Jobs\Firebase\FirebaseJob;
use App\Models\System;
use App\Services\Firebase;
use Illuminate\Support\Collection;

class UploadSystemsJob extends FirebaseJob
{
    /**
     * Create a new job instance.
     */
    public function __construct(
        private ?Collection $systemIds = null
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        parent::handle();
        if (!$this->systemIds) {
            System::select('id')
                ->pluck('id')
                ->chunk(100)
                ->each(fn (Collection $ids) => static::dispatch($ids));

            return;
        }

        /** @var Firebase */
        $firebase = app(Firebase::class);

        System::findMany($this->systemIds)
            ->each(fn (System $system) => $firebase->uploadSystem($system));
    }
}
