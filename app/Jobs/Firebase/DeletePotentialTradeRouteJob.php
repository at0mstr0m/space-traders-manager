<?php

declare(strict_types=1);

namespace App\Jobs\Firebase;

class DeletePotentialTradeRouteJob extends FirebaseJob
{
    /**
     * Create a new job instance.
     */
    public function __construct(
        private string $key
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        parent::handle();
        $this->firebase->deletePotentialTradeRoute($this->key);
    }
}
