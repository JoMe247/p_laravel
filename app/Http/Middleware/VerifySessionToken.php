<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class VerifySessionToken
{
    public function handle(Request $request, Closure $next)
    {
        // Determinar qué tipo de usuario está autenticado
        $guard = Auth::guard('web')->check() ? 'web' : (Auth::guard('sub')->check() ? 'sub' : null);
        $user  = Auth::guard($guard)->user();

        // Si no hay usuario autenticado, continuar
        if (!$user) {
            return $next($request);
        }

        // Solo aplicar verificación estricta de token a usuarios "web"
        if ($guard === 'web') {
            $sessionToken = session('session_token');

            if (!$user->current_session_token || $user->current_session_token !== $sessionToken) {
                Auth::guard('web')->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('login')->withErrors([
                    'duplicate' => 'Tu sesión se cerró porque se inició en otro dispositivo.',
                ]);
            }
        }

        // Si es sub_user, no aplicar verificación de token (evita bucle)
        return $next($request);
    }
}
