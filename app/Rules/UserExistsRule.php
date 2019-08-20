<?php


namespace App\Rules;


use Illuminate\Contracts\Validation\Rule;

class UserExistsRule implements Rule
{
    /**
     * Determine if the validation rule passes.
     *
     * @param string $attribute
     * @param mixed $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        // TODO: Check auth database to see if user exists
        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string|array
     */
    public function message()
    {
        return 'This user does not exists';
    }
}
