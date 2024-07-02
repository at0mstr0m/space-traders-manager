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
        $waypoint = $this->getOrFetchWaypoint($this->waypointSymbol);

        // connections are already set
        if ($waypoint->system->connections()->exists()) {
            return;
        }

        $this->api
            ->getJumpGate($this->waypointSymbol)
            ->connections
            ->map(
                fn (string $connectedWaypointSymbol) => [
                    'system' => $this->getOrFetchWaypoint($connectedWaypointSymbol)->system,
                    'connectedWaypointSymbol' => $connectedWaypointSymbol,
                ]
            )->pipe(
                function (Collection $data) use ($waypoint): Collection {
                    $waypoint->system
                        ->connections()
                        ->sync($data->pluck('system'));

                    return $data;
                }
            )->filter(
                fn (array $data) => $data['system']
                    ->connections()
                    ->doesntExist()
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
