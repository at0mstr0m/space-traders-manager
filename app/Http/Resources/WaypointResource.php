<?php

namespace App\Http\Resources;

use App\Enums\WaypointTypes;
use App\Models\System;
use App\Models\Waypoint;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WaypointResource extends JsonResource
{
    public bool $includeConnectedWaypoints = true;

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
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
            'connected_waypoints' => $this->when(
                $this->includeConnectedWaypoints
                && $this->type === WaypointTypes::JUMP_GATE,
                $this->system->connections->map(
                    fn (System $system) => static::withoutConnectedWaypoints($system->jumpGate)
                )
            ),
        ];
    }

    public static function withoutConnectedWaypoints(Waypoint $waypoint): self
    {
        $resource = new static($waypoint);
        $resource->includeConnectedWaypoints = false;

        return $resource;
    }
}
