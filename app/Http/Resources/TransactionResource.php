<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
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
            'agent_symbol' => $this->agent_symbol,
            'ship_symbol' => $this->ship_symbol,
            'waypoint_symbol' => $this->waypoint_symbol,
            'timestamp' => $this->timestamp->toDateTimeString(),
            'type' => $this->type->value,
            'trade_symbol' => $this->trade_symbol?->value,
            'units' => $this->units,
            'price_per_unit' => $this->price_per_unit,
            'total_price' => $this->total_price,
        ];
    }
}
