<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Actions\UpdateWaypointAction;
use App\Helpers\SpaceTraders;
use App\Models\Waypoint;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class FetchSystemConnectionsJob implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;

    protected ?SpaceTraders $api = null;

    /**
     * Create a new job instance.
     */
    public function __construct(
        private string $waypointSymbol
    ) {
        $this->api ??= app(SpaceTraders::class);
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $currentWaypoint = $this->getOrFetchWaypoint($this->waypointSymbol);

        Log::debug("Fetching connections to Waypoint {$this->waypointSymbol}");
        if (!$currentWaypoint->faction && $currentWaypoint->ships()->doesntExist()) {
            Log::warning("Waypoint {$this->waypointSymbol} has no faction and no ships present, cannot fetch connections.");

            return;
        }

        if ($currentWaypoint->system->connections()->exists()) {
            Log::debug("System {$currentWaypoint->system->symbol} already has connections.");

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
}
