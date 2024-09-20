<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class ValidUserType implements Rule
{
    public function passes($attribute, $value)
    {
        $validTypes = ['doctor', 'user'];
        return in_array($value, $validTypes);
    }

    public function message()
    {
        return 'The :attribute must be either doctor or user.';
    }
}
