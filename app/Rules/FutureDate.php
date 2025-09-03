<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class FutureDate implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value) {
            $selectedDate = \Carbon\Carbon::parse($value);
            $now = \Carbon\Carbon::now();
            
            if ($selectedDate->lte($now)) {
                $fail('Trường :attribute phải là ngày giờ trong tương lai.');
            }
        }
    }
}
