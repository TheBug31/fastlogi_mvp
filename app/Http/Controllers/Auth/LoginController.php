<?php

namespace App\Http\Controllers\Auth;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class LoginController extends Controller
{
    /**
     * Show the application login form.
     */
    public function showLoginForm(): View|RedirectResponse
    {
        if (Auth::check()) {
            return $this->redirectForUser(Auth::user());
        }

        return view('auth.login');
    }

    /**
     * Handle an authentication attempt.
     */
    public function login(Request $request): RedirectResponse
    {
        $request->validate([
            'identifier' => ['required', 'string'],
            'password' => ['required', 'string'],
            'remember' => ['nullable', 'boolean'],
        ]);

        $identifier = trim($request->input('identifier'));

        // Resolve user by email or by phone
        $user = User::where('email', $identifier)
            ->orWhere('phone', $identifier)
            ->first();

        if (! $user || ! Hash::check($request->input('password'), $user->password)) {
            return back()->withInput($request->only('identifier', 'remember'))->withErrors([
                'identifier' => 'El correo o la contraseña ingresada no coinciden con nuestros registros del sistema.',
            ]);
        }

        // Reject suspended/inactive accounts and owners of suspended businesses
        // (RF-003, RF-010). Rules live in User::canLogin() to avoid duplication.
        if (! $user->canLogin()) {
            return back()->withInput($request->only('identifier', 'remember'))
                ->with('suspended', true)
                ->withErrors(['identifier' => 'suspended']);
        }

        // Log the user in
        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        return $this->redirectForUser($user);
    }

    /**
     * Log the user out of the application.
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    /**
     * Redirect authenticated user according to their role.
     */
    protected function redirectForUser(User $user): RedirectResponse
    {
        return match ($user->role) {
            UserRole::Superadmin => redirect()->intended(route('superadmin.dashboard')),
            UserRole::Owner => redirect()->intended(route('owner.dashboard')),
            UserRole::Driver => redirect()->intended(route('driver.routes')),
        };
    }
}
