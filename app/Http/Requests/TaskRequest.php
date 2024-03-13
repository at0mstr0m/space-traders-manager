<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\TaskTypes;
use App\Models\Waypoint;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TaskRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<mixed>|\Illuminate\Contracts\Validation\ValidationRule|string>
     */
    public function rules(): array
    {
        return [
            'type' => [
                'required',
                'string',
                Rule::enum(TaskTypes::class),
            ],
            'payload' => [
                'required_if:type,' . TaskTypes::COLLECTIVE_MINING->value
                    . ',' . TaskTypes::COLLECTIVE_SIPHONING->value
                    . ',' . TaskTypes::SUPPORT_COLLECTIVE_MINERS->value,
            ],
            'payload.extraction_location' => [
                'required_if:type,' . TaskTypes::COLLECTIVE_MINING->value
                    . ',' . TaskTypes::COLLECTIVE_SIPHONING->value,
                'prohibited_unless:type,' . TaskTypes::COLLECTIVE_MINING->value
                    . ',' . TaskTypes::COLLECTIVE_SIPHONING->value,
                'string',
                Rule::exists(Waypoint::class, 'symbol'),
            ],
            'payload.waiting_location' => [
                'required_if:type,' . TaskTypes::SUPPORT_COLLECTIVE_MINERS->value,
                'prohibited_unless:type,' . TaskTypes::SUPPORT_COLLECTIVE_MINERS->value
                    . ',' . TaskTypes::COLLECTIVE_SIPHONING->value,
                'string',
                Rule::exists(Waypoint::class, 'symbol'),
            ],
        ];
    }
}
