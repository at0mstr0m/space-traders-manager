<?php

namespace App\Jobs;

use App\Jobs\Firebase\FirebaseJob;
use App\Models\System;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class DownloadSystemsJob extends FirebaseJob
{
    /**
     * Create a new job instance.
     */
    public function __construct(
        private ?Collection $symbols = null
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        parent::handle();

        if (!$this->symbols) {
            $this->firebase
                ->getSystemSymbols()
                ->chunk(100)
                ->each(fn (Collection $systemSymbols) => static::dispatch($systemSymbols));

            return;
        }

        $this->symbols
            // todo: maybe first() is not needed when set() is used in Firebase::uploadSystem()
            ->map(fn (string $symbol) => $this->firebase->getSystem($symbol)->first())
            ->each(fn (array $systemData) => System::updateOrCreate(
                ['symbol' => $systemData['symbol']],
                Arr::except($systemData, 'symbol')
            ));
    }
}
