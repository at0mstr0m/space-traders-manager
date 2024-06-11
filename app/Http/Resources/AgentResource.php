<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AgentResource extends JsonResource
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
            'headquarters' => $this->headquarters,
            'credits' => $this->credits,
            'starting_faction' => $this->starting_faction,
            'ship_count' => $this->ship_count,
            'starting_system' => SystemResource::make($this->starting_system),
        ];
    }
}
