<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Actions\UpdateWaypointAction;
use App\Helpers\SpaceTraders;
use App\Jobs\Firebase\UploadSystemsJob;
use App\Models\System;
use App\Models\Waypoint;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUniqueUntilProcessing;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class FetchSystemConnectionsJob implements ShouldQueue, ShouldBeUniqueUntilProcessing
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;

    private const CACHE_KEY = 'fetching_connections';

    protected ?SpaceTraders $api = null;

    /**
     * Create a new job instance.
     */
    public function __construct(
        private ?string $waypointSymbol = null
    ) {
        $this->api ??= app(SpaceTraders::class);
        Cache::increment(static::CACHE_KEY);
    }

    /**
     * Get the middleware the job should pass through.
     *
     * @return array<int, object>
     */
    public function middleware(): array
    {
        return [new WithoutOverlapping($this->waypointSymbol ?? '')];
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->waypointSymbol ??= System::first()->jumpGate->symbol;

        $currentWaypoint = $this->getOrFetchWaypoint($this->waypointSymbol);

        Log::debug("Fetching connections to Waypoint {$this->waypointSymbol}");
        if (!$currentWaypoint->faction_id && $currentWaypoint->ships()->doesntExist()) {
            Log::warning("Waypoint {$this->waypointSymbol} has no faction and no ships present, cannot fetch connections.");

            $this->count();

            return;
        }

        if ($currentWaypoint->system->connections()->exists()) {
            Log::debug("System {$currentWaypoint->system->symbol} already has connections.");

            $this->count();

            return;
        }

        $this->api
            ->getJumpGate($this->waypointSymbol)
            ->connections
            ->map(
                function (string $connectedWaypointSymbol) {
                    $waypoint = $this->getOrFetchWaypoint($connectedWaypointSymbol);

                    return [
                        'waypoint' => $waypoint,
                        'system' => $waypoint->system,
                        'connectedWaypointSymbol' => $connectedWaypointSymbol,
                    ];
                }
            )->pipe(
                function (Collection $data) use ($currentWaypoint): Collection {
                    $currentWaypoint->system
                        ->connections()
                        ->sync($data->pluck('system')->pluck('id'));

                    return $data;
                }
            )->filter(
                fn (array $data) => $data['system']->connections()->doesntExist()
            )->each(
                fn (array $connectedWaypoint) => static::dispatch(
                    $connectedWaypoint['connectedWaypointSymbol']
                )
            );

        $this->count();
    }

    /**
     * Get the unique ID for the job.
     */
    public function uniqueId(): string
    {
        return static::class . ':' . $this->waypointSymbol;
    }

    private function getOrFetchWaypoint(string $waypointSymbol): Waypoint
    {
        return Waypoint::findBySymbol($waypointSymbol)
            ?? UpdateWaypointAction::run(
                $this->api->getWaypoint($waypointSymbol)
            );
    }

    private function count(): void
    {
        Cache::decrement(static::CACHE_KEY);

        if ((int) Cache::get(static::CACHE_KEY) === 0) {
            UploadSystemsJob::dispatch();
        }
    }
}
