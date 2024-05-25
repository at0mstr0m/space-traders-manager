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
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
            'symbol' => $this->symbol,
            'role' => $this->role,
            'waypoint_symbol' => $this->waypoint_symbol,
            'destination' => $this->destination,
            'has_reached_destination' => $this->has_reached_destination,
            'can_refuel_at_current_location' => $this->can_refuel_at_current_location,
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
            'frame_integrity' => $this->frame_integrity,
            'reactor_condition' => $this->reactor_condition,
            'reactor_integrity' => $this->reactor_integrity,
            'engine_condition' => $this->engine_condition,
            'engine_integrity' => $this->engine_integrity,
            'cargo_capacity' => $this->cargo_capacity,
            'cargo_units' => $this->cargo_units,
            'frame' => FrameResource::make($this->loadMissing('frame')->frame),
            'reactor' => ReactorResource::make($this->loadMissing('reactor')->reactor),
            'engine' => EngineResource::make($this->loadMissing('engine')->engine),
            'modules' => ShipModuleResource::collection($this->loadMissing('modules')->modules),
            'mounts' => ShipMountResource::collection($this->loadMissing('mounts')->mounts),
            'cargos' => CargoResource::collection($this->loadMissing('cargos')->cargos),
            'task' => TaskResource::make($this->loadMissing('task')->task),
        ];
    }
}
