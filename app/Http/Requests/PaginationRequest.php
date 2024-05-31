<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PaginationRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule', 'array<mixed>', 'string>
     */
    public function rules(): array
    {
        return [
            'page' => [
                'sometimes',
                'integer',
                'min:1',
            ],
            'perPage' => [
                'sometimes',
                'integer',
                'min:1',
                'max:1000',
            ],
            'sortBy' => [
                'required_with:sortDirection',
                'string',
            ],
            'sortDirection' => [
                'required_with:sortBy',
                'string',
                Rule::in(['asc', 'desc']),
            ],
        ];
    }

    public function page(): int
    {
        return (int) $this->validated('page', 1);
    }

    public function perPage(): int
    {
        return (int) $this->validated('perPage', 10);
    }

    public function hasSort(): bool
    {
        return $this->has('sortBy') && $this->has('sortDirection');
    }

    public function sortBy(?string $default = null): ?string
    {
        return (string) $this->validated('sortBy') ?: $default;
    }

    public function sortDirection(?string $default = null): ?string
    {
        return (string) $this->validated('sortDirection') ?: $default;
    }
}
