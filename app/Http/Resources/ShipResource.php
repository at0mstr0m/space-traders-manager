<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ShipResource extends JsonResource
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
            'symbol' => $this->symbol,
            'role' => $this->role,
            'waypoint_symbol' => $this->waypoint_symbol,
            'status' => $this->status,
            'flight_mode' => $this->flight_mode,
            'crew_current' => $this->crew_current,
            'crew_capacity' => $this->crew_capacity,
            'crew_required' => $this->crew_required,
            'crew_rotation' => $this->crew_rotation,
            'crew_morale' => $this->crew_morale,
            'crew_wages' => $this->crew_wages,
            'fuel_current' => $this->fuel_current,
            'fuel_capacity' => $this->fuel_capacity,
            'fuel_consumed' => $this->fuel_consumed,
            'cooldown' => $this->cooldown,
            'frame_condition' => $this->frame_condition,
            'reactor_condition' => $this->reactor_condition,
            'engine_condition' => $this->engine_condition,
            'cargo_capacity' => $this->cargo_capacity,
            'cargo_units' => $this->cargo_units,
        ];
    }
}
