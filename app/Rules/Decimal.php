<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class Decimal implements Rule
{
    /**
     * Determine if the validation rule passes.
     *
     * @param string $attribute
     * @param mixed  $value
     *
     * @return bool
     */
    public function passes($attribute, $value)
    {
        return preg_match("/^\d{1,8}\.\d{2}$/", $value) === 1;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The :attribute must be a positive decimal (example: 1.00).';
    }
}
