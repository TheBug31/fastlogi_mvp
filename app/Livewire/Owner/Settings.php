<?php

namespace App\Livewire\Owner;

use App\Models\Business;
use App\Rules\CurrentPassword;
use Illuminate\Validation\Rule;
use Livewire\Component;

/**
 * Owner account/business settings.
 *
 * Two read/write blocks: the business profile (name, address, phone) and the
 * signed-in owner's own credentials (name, phone, email and a voluntary
 * password change). Businesses without an `email` column keep using the owner's
 * e-mail as the contact address.
 */
class Settings extends Component
{
    public string $name = '';

    public string $address = '';

    public string $phone = '';

    public string $owner_name = '';

    public string $owner_email = '';

    public string $owner_phone = '';

    public string $current_password = '';

    public string $password = '';

    public string $password_confirmation = '';

    public function mount(): void
    {
        $this->fillFromModels();
    }

    public function updateBusiness(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:30'],
        ], [], [
            'name' => 'nombre del negocio',
            'address' => 'dirección',
            'phone' => 'teléfono',
        ]);

        $this->business()->update($validated);

        session()->flash('success', 'Datos del negocio actualizados.');
    }

    public function updateAccount(): void
    {
        $user = auth()->user();

        $validated = $this->validate([
            'owner_name' => ['required', 'string', 'max:255'],
            'owner_email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'owner_phone' => ['required', 'string', 'max:30'],
        ], [], [
            'owner_name' => 'nombre',
            'owner_email' => 'correo',
            'owner_phone' => 'teléfono',
        ]);

        $user->update([
            'name' => $validated['owner_name'],
            'email' => $validated['owner_email'],
            'phone' => $validated['owner_phone'],
        ]);

        session()->flash('success', 'Tu cuenta quedó actualizada.');
    }

    public function updatePassword(): void
    {
        $validated = $this->validate([
            'current_password' => ['required', 'string', new CurrentPassword],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'current_password.required' => 'Debes ingresar tu contraseña actual.',
            'password.required' => 'Debes ingresar la nueva contraseña.',
            'password.min' => 'La nueva contraseña debe tener al menos 8 caracteres.',
            'password.confirmed' => 'La confirmación no coincide con la nueva contraseña.',
        ], [
            'current_password' => 'contraseña actual',
            'password' => 'nueva contraseña',
        ]);

        auth()->user()->update(['password' => $validated['password']]);

        $this->reset(['current_password', 'password', 'password_confirmation']);

        session()->flash('success', 'Contraseña actualizada correctamente.');
    }

    public function render()
    {
        $business = $this->business();

        return view('livewire.owner.settings', [
            'business' => $business,
            'stats' => [
                'repartidores' => $business->deliveryDrivers()->count(),
                'productos' => $business->products()->count(),
                'pedidos' => $business->orders()->count(),
            ],
        ]);
    }

    private function business(): Business
    {
        return auth()->user()->business;
    }

    private function fillFromModels(): void
    {
        $user = auth()->user();
        $business = $user->business;

        $this->name = $business->name;
        $this->address = $business->address;
        $this->phone = $business->phone;

        $this->owner_name = $user->name;
        $this->owner_email = $user->email;
        $this->owner_phone = $user->phone ?? '';
    }
}
