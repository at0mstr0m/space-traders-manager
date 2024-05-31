<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WaypointResource extends JsonResource
{
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
        ];
    }
}
