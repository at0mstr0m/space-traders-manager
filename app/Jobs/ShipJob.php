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
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

abstract class ShipJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    protected array $constructorArguments = [];

    protected ?SpaceTraders $api = null;

    protected ?Ship $ship = null;

    /**
     * Create a new job instance.
     */
    public function __construct(protected string $shipSymbol)
    {
        $this->constructorArguments = func_get_args();
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->initShip();
        $this->log('START');

        if ($this->ship->is_in_transit || $this->ship->cooldown) {
            $this->log('is in transit or on cooldown');
            $this->selfDispatch()->delay($this->ship->cooldown ?: 60);

            return;
        }

        if (!$this->ship->has_reached_destination) {
            $this->log("has not reached its destination, currently at {$this->ship->waypoint_symbol}");
            $this->flyToLocation($this->ship->destination);

            return;
        }

        $this->handleShip();
        $this->log('END');
    }

    /**
     * Handle a job failure.
     */
    public function failed(?\Throwable $exception): void
    {
        Log::channel('ship_jobs')
            ->error(
                ($this->ship?->symbol ?? $this->shipSymbol)
                . ' '
                . static::class
                . ' FAILED: '
                . ($exception ? $exception->getMessage() : 'null')
            );
    }

    abstract protected function handleShip(): void;

    protected function selfDispatch(array $arguments = []): PendingDispatch
    {
        return static::dispatch(...[
            ...$this->constructorArguments,
            ...$arguments,
        ]);
    }

    protected function flyToLocation(string $waypointSymbol): void
    {
        $this->log("fly to {$waypointSymbol}");

        $cooldown = $this->ship
            ->navigateTo($waypointSymbol)
            ->cooldown;
        $this->selfDispatch()->delay($cooldown);
    }

    protected function initApi(): void
    {
        $this->api ??= app(SpaceTraders::class);
    }

    protected function log(string $message, array $replacements = []): void
    {
        Log::channel('ship_jobs')
            ->info(
                $this->ship->symbol
                . ' '
                . static::class
                . ' "'
                . Str::replaceArray(':?', $replacements, $message)
                . '"'
            );
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
