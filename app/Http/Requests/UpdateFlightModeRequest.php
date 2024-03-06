<?php

namespace App\Http\Requests;

use App\Enums\FlightModes;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateFlightModeRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<mixed>|\Illuminate\Contracts\Validation\ValidationRule|string>
     */
    public function rules(): array
    {
        return [
            'flightMode' => [
                'required',
                'string',
                Rule::enum(FlightModes::class),
            ],
        ];
    }
}
