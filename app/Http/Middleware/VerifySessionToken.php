<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VerifySessionToken
{
    public function handle(Request $request, Closure $next)
    {
        $guard = null;

        if (Auth::guard('web')->check()) $guard = 'web';
        elseif (Auth::guard('sub')->check()) $guard = 'sub';

        if (!$guard) {
            return $next($request);
        }

        $user = Auth::guard($guard)->user();
        if (!$user) {
            return $next($request);
        }

        // Solo "web" valida token estricto
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

        return $next($request);
    }
}
