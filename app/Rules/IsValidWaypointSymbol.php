<?php

declare(strict_types=1);

namespace App\Rules;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Str;

class IsValidWaypointSymbol implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, \Closure $fail): void
    {
        if (!is_string($value)) {
            $fail(Str::replace('attribute', $attribute, 'The :attribute must be a string.'));
        }

        if (!Str::isMatch('/^X[1-9]-[A-Z]{2}[1-9]\d?-(([A-Z]{2}[1-9][A-Z])|([A-Z][1-9]\d?))$/', $value)) {
            $fail(Str::replace('attribute', $attribute, 'The :attribute must be a valid waypoint symbol.'));
        }
    }
}
