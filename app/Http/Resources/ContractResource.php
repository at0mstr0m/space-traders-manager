<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ContractResource extends JsonResource
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
            'identification' => $this->identification,
            'faction_symbol' => $this->faction_symbol,
            'type' => $this->type,
            'accepted' => $this->accepted,
            'fulfilled' => $this->fulfilled,
            'deadline' => $this->deadline->toDateTimeString(),
            'payment_on_accepted' => $this->payment_on_accepted,
            'payment_on_fulfilled' => $this->payment_on_fulfilled,
            'deliveries' => DeliveryResource::collection($this->loadMissing('deliveries')->deliveries),
        ];
    }
}
