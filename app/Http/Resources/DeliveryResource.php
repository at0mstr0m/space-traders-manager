<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DeliveryResource extends JsonResource
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
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'trade_symbol' => $this->trade_symbol,
            'destination_symbol' => $this->destination_symbol,
            'units_required' => $this->units_required,
            'units_fulfilled' => $this->units_fulfilled,
        ];
    }
}
