<?php

declare(strict_types=1);

namespace App\Jobs\Firebase;

use App\Models\PotentialTradeRoute;

class UploladPotentialTradeRouteJob extends FirebaseJob
{
    /**
     * Create a new job instance.
     */
    public function __construct(
        private int $routeId
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $route = PotentialTradeRoute::find($this->routeId);
        if (!$route) {
            throw new \Exception("Route with id {$this->routeId} does not exist anymore", 1);
        }
        parent::handle();
        $this->firebase->uploadPotentialTradeRoute($route);
    }
}
