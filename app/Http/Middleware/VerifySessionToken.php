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
        if (Auth::check()) {
            $user = Auth::user();

            // Verifica si el token de sesión en la DB sigue siendo el mismo
            $sessionToken = session('session_token');

            if (!$user->current_session_token || $user->current_session_token !== $sessionToken) {
                // Token inválido: cerramos sesión y redirigimos
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('login')->withErrors([
                    'duplicate' => 'Tu sesión se cerró porque se inició en otro dispositivo.',
                ]);
            }
        }

        return $next($request);
    }
}
