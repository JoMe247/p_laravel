<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RedirectIfAuthenticated
{
    /**
     * Maneja una solicitud entrante.
     */
    public function handle(Request $request, Closure $next)
    {
        // Si está autenticado en cualquiera de los dos guards
        if (Auth::guard('web')->check() || Auth::guard('sub')->check()) {
            // Si intenta acceder a login o register, lo mandamos al dashboard
            if ($request->is('login') || $request->is('register')) {
                return redirect('/dashboard');
            }
        } else {
            // Si NO está autenticado y trata de acceder al dashboard
            if ($request->is('dashboard')) {
                return redirect('/login');
            }
        }

        return $next($request);
    }
}
