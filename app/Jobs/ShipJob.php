<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Helpers\SpaceTraders;
use App\Models\Ship;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Bus\PendingDispatch;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

abstract class ShipJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    protected array $constructorParams = [];

    protected ?SpaceTraders $api = null;

    /**
     * Create a new job instance.
     */
    public function __construct(
        protected string $shipSymbol,
        protected ?Ship $ship = null,
    ) {
        $this->constructorParams = func_get_args();
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->initShip();

        if ($this->ship->is_in_transit || $this->ship->cooldown) {
            dump("{$this->ship->symbol} is in transit or on cooldown");
            $this->selfDispatch()->delay($this->ship->cooldown);

            return;
        }

        $this->handleShip();
    }

    abstract protected function handleShip(): void;

    protected function selfDispatch(array $params = []): PendingDispatch
    {
        return static::dispatch(
            ...$this->constructorParams,
            ...$params
        );
    }

    protected function flyToLocation(string $waypointSymbol): void
    {
        dump("fly to {$waypointSymbol}");
        $cooldown = $this->ship
            ->refuel()
            ->navigateTo($waypointSymbol)
            ->cooldown;
        $this->selfDispatch()->delay($cooldown);
    }

    protected function initApi(): void
    {
        $this->api ??= app(SpaceTraders::class);
    }

    private function initShip(): void
    {
        $this->ship ??= Ship::findBySymbol($this->shipSymbol);
        if (!$this->ship) {
            throw new \Exception("Ship not found: {$this->shipSymbol}");
        }
        $this->ship = $this->ship->refetch();
    }
}
