<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PotentialTradeRouteResource extends JsonResource
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
            'trade_symbol' => $this->trade_symbol,
            'origin' => $this->origin,
            'destination' => $this->destination,
            'profit' => $this->profit,
            'profit_per_flight' => $this->profit_per_flight,
            'purchase_price' => $this->purchase_price,
            'supply_at_origin' => $this->supply_at_origin,
            'activity_at_origin' => $this->activity_at_origin,
            'trade_volume_at_origin' => $this->trade_volume_at_origin,
            'sell_price' => $this->sell_price,
            'supply_at_destination' => $this->supply_at_destination,
            'activity_at_destination' => $this->activity_at_destination,
            'trade_volume_at_destination' => $this->trade_volume_at_destination,
            'distance' => $this->distance,
            'ship' => $this->ship_id ? new ShipResource($this->ship) : null,
        ];
    }
}
