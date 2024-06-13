<?php

declare(strict_types=1);

namespace App\Jobs\Firebase;

use App\Models\Ship;

class SetShipRelationJob extends FirebaseJob
{
    /**
     * Create a new job instance.
     */
    public function __construct(
        private int $shipId
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $ship = Ship::find($this->shipId);
        if (!$ship) {
            throw new \Exception("Ship with id {$this->shipId} does not exist anymore", 1);
        }
        parent::handle();
        $this->firebase->setShipTaskRelation($ship);
    }
}
