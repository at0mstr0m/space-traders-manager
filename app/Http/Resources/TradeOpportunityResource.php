<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TradeOpportunityResource extends JsonResource
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
            'waypoint_symbol' => $this->waypoint_symbol,
            'purchase_price' => $this->purchase_price,
            'sell_price' => $this->sell_price,
            'type' => $this->type,
            'trade_volume' => $this->trade_volume,
            'supply' => $this->supply,
            'activity' => $this->activity,
        ];
    }
}
