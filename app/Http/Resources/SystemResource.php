<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SystemResource extends JsonResource
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
            'sector_symbol' => $this->sector_symbol,
            'type' => $this->type,
            'x' => $this->x,
            'y' => $this->y,
            'faction' => $this->faction ? new FactionResource($this->faction) : null,
            'ships_count' => $this->ships()->count(),
            'waypoints_count' => $this->waypoints()->count(),
        ];
    }
}
