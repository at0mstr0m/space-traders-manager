<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ShipMountResource extends JsonResource
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
            'name' => $this->name,
            'description' => $this->description,
            'strength' => $this->strength,
            'required_power' => $this->required_power,
            'required_crew' => $this->required_crew,
            'quantity' => $this->pivot->quantity,
        ];
    }
}
