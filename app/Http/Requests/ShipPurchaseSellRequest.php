<?php

namespace App\Http\Requests;

use App\Enums\TradeSymbols;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ShipPurchaseSellRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<mixed>|\Illuminate\Contracts\Validation\ValidationRule|string>
     */
    public function rules(): array
    {
        return [
            'symbol' => [
                'required',
                Rule::enum(TradeSymbols::class),
            ],
            'quantity' => [
                'required',
                'integer',
                'min:1',
            ],
        ];
    }
}
