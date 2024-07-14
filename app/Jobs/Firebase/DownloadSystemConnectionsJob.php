<?php

declare(strict_types=1);

namespace App\Jobs\Firebase;

use App\Models\System;
use Illuminate\Support\Collection;

class DownloadSystemConnectionsJob extends FirebaseJob
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
            ->each(function (array $systemData) {
                // if the system has no connections, it can be skipped
                if (!isset($systemData['connections'])) {
                    return;
                }

                $connectedSystems = System::getquery()
                    ->select('id')
                    ->whereIn('symbol', $systemData['connections'])
                    ->pluck('id');
                System::findBySymbol($systemData['symbol'])
                    ->connections()
                    ->sync($connectedSystems);
            });
    }
}
