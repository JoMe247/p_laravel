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
        // Si el usuario ya está autenticado...
        if (Auth::check()) {
            // Y está intentando acceder al login o register
            if ($request->is('login') || $request->is('register')) {
                // Redirigirlo al dashboard
                return redirect('/dashboard');
            }
        } else {
            // Si NO está autenticado y trata de acceder a dashboard
            if ($request->is('dashboard')) {
                return redirect('/login');
            }
        }

        return $next($request);
    }
}
