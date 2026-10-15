<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (! Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        // 1. Account status check
        if (! $user->isActive()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')
                ->with('suspended', true)
                ->withErrors(['identifier' => 'Tu cuenta se encuentra suspendida o inactiva.']);
        }

        // 2. Owner's business status check
        if ($user->isOwner() && $user->business && $user->business->isSuspended()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')
                ->with('suspended', true)
                ->withErrors(['identifier' => 'El comercio se encuentra temporalmente suspendido.']);
        }

        // 3. Role authorization check
        $userRoleValue = $user->role instanceof UserRole ? $user->role->value : (string) $user->role;

        if (! empty($roles) && ! in_array($userRoleValue, $roles, true)) {
            abort(403, 'No tienes permisos para acceder a esta sección.');
        }

        return $next($request);
    }
}
