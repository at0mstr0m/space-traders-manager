<?php

namespace App\Http\Requests;

use App\Enums\ShipTypes;
use App\Rules\IsValidWaypointSymbol;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PurchaseShipRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<mixed>|\Illuminate\Contracts\Validation\ValidationRule|string>
     */
    public function rules(): array
    {
        return [
            'shipType' => [
                'required',
                'string',
                Rule::in(ShipTypes::values()),
            ],
            'waypointSymbol' => [
                'required',
                'string',
                new IsValidWaypointSymbol(),
            ],
        ];
    }
}
