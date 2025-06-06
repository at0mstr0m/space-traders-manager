<?php

declare(strict_types=1);

namespace App\Jobs\Firebase;

use App\Models\System;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class DownloadSystemsJob extends FirebaseJob
{
    /**
     * Create a new job instance.
     */
    public function __construct(private ?Collection $symbols = null) {}

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
            ->map(fn (string $symbol) => $this->firebase->getSystem($symbol)->all())
            ->each(fn (array $systemData) => System::updateOrCreate(
                ['symbol' => $systemData['symbol']],
                Arr::except($systemData, ['symbol', 'connections'])
            ));

        // all systems have been downloaded
        // now download the systems' connections
        if (System::count() === $this->firebase->getSystemSymbols()->count()) {
            DownloadSystemConnectionsJob::dispatch();
        }
    }
}
