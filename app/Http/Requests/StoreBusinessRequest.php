<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBusinessRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isSuperadmin() ?? false;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:30'],
            'owner_name' => ['required', 'string', 'max:255'],
            'owner_email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'owner_phone' => ['nullable', 'string', 'max:30'],
            'password' => ['required', 'string', 'min:8'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'El nombre del negocio es obligatorio.',
            'address.required' => 'La dirección del negocio es obligatoria.',
            'phone.required' => 'El teléfono del negocio es obligatorio.',
            'owner_name.required' => 'El nombre del dueño es obligatorio.',
            'owner_email.required' => 'El correo del dueño es obligatorio.',
            'owner_email.email' => 'El correo del dueño debe ser una dirección válida.',
            'owner_email.unique' => 'Ya existe un usuario con ese correo electrónico.',
            'password.required' => 'La contraseña inicial es obligatoria.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
        ];
    }
}
