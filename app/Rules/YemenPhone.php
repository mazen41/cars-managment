<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class YemenPhone implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!is_string($value) || !preg_match('/^\+967(77|71|73|70)\d{7}$/', $value)) {
            $fail('The :attribute must be a valid Yemeni phone number starting with +967 and one of the following prefixes: 77, 71, 73, 70.');
        }
    }
}
