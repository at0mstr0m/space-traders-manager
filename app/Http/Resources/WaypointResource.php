<?php

namespace App\Http\Resources;

use App\Actions\UpdateWaypointAction;
use App\Enums\WaypointTypes;
use App\Helpers\SpaceTraders;
use App\Models\System;
use App\Models\Waypoint;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;

class WaypointResource extends JsonResource
{
    public bool $includeConnectedWaypoints = true;

    private ?Collection $connectedJumpGates = null;

    private SpaceTraders $api;

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var SpaceTraders */
        $this->api = app(SpaceTraders::class);

        return [
            'id' => $this->id,
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
            'symbol' => $this->symbol,
            'type' => $this->type->value,
            'faction' => $this->faction ? new FactionResource($this->faction) : null,
            'x' => $this->x,
            'y' => $this->y,
            'orbits' => $this->orbits ? new static($this->orbitedWaypoint) : null,
            'is_under_construction' => $this->is_under_construction,
            'traits' => WaypointTraitResource::collection($this->traits),
            'ship_count' => $this->whenCounted('ships'),
            'connected_waypoints' => $this->includeConnectedWaypoints && $this->type === WaypointTypes::JUMP_GATE
                ? $this->system->connections->map(function (System $system) {
                    if ($system->jumpGate) {
                        return static::withoutConnectedWaypoints($system->jumpGate);
                    }
                    $connectedJumpGateSymbols ??= $this->api
                        ->getJumpGate($this->symbol)
                        ->connections
                        ->each(function (string $symbol) {
                            if (Waypoint::where('symbol', $symbol)->doesntExist()) {
                                $jumpGate = UpdateWaypointAction::run(
                                    $this->api->getWaypoint($symbol)
                                );
                            }
                        });

                    // $jumpGate = UpdateWaypointAction::run(
                    //     $this->api->getWaypoint($this->symbol)
                    // );
                })
                : null,
        ];
    }

    public static function withoutConnectedWaypoints(Waypoint $waypoint): self
    {
        $resource = new static($waypoint);
        $resource->includeConnectedWaypoints = false;

        return $resource;
    }
}
