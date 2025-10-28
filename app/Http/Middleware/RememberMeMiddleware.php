<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Illuminate\Support\Facades\Cookie;

class RememberMeMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        // Si ya está autenticado, continuar
        if (Auth::check()) {
            return $next($request);
        }

        // Si hay cookie "rememberme_token"
        $token = $request->cookie('rememberme_token');

        if ($token) {
            $record = DB::table('user_tokens')
                ->where('token', $token)
                ->where('expires_at', '>', now())
                ->first();

            if ($record) {
                $user = User::find($record->user_id);

                if ($user) {
                    // Iniciar sesión automáticamente
                    Auth::login($user, true);

                    // Regenerar la sesión para seguridad
                    $request->session()->regenerate();

                    // Refrescar la expiración de la cookie (opcional)
                    Cookie::queue('rememberme_token', $token, 60 * 24 * 30);

                    // Redirigir al dashboard directamente
                    return redirect()->route('dashboard');
                }
            } else {
                // Si el token ya no es válido o expiró, borrar cookie
                Cookie::queue(Cookie::forget('rememberme_token'));
            }
        }

        return $next($request);
    }
}
