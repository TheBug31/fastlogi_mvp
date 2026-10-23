<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Hash;

/**
 * Ensures the given value matches the signed-in user's current password.
 */
class CurrentPassword implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $user = auth()->user();

        if (! $user || ! is_string($value) || ! Hash::check($value, $user->password)) {
            $fail('La contraseña actual no es correcta.');
        }
    }
}
