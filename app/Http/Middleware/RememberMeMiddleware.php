<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cookie;
use App\Models\User;
use App\Models\SubUser;

class RememberMeMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        // Si ya está autenticado, continuar
        if (Auth::guard('web')->check() || Auth::guard('sub')->check()) {
            return $next($request);
        }

        $cookie = $request->cookie('rememberme_token');
        if (!$cookie) {
            return $next($request);
        }

        // Esperamos formato: "web|TOKEN" o "sub|TOKEN"
        [$type, $token] = array_pad(explode('|', $cookie, 2), 2, null);

        if (!$type || !$token) {
            Cookie::queue(Cookie::forget('rememberme_token'));
            return $next($request);
        }

        if ($type === 'web') {
            $record = DB::table('user_tokens')
                ->where('token', $token)
                ->where('expires_at', '>', now())
                ->first();

            if ($record) {
                $user = User::find($record->user_id);
                if ($user) {
                    Auth::guard('web')->login($user, true);
                    $request->session()->regenerate();

                    // Sliding expiration (opcional pero recomendado)
                    DB::table('user_tokens')->where('id', $record->id)->update([
                        'expires_at' => now()->addDays(30),
                    ]);
                    Cookie::queue('rememberme_token', 'web|' . $token, 60 * 24 * 30);

                    // Si está en /login, lo sacamos a dashboard
                    if ($request->is('login')) {
                        return redirect()->route('dashboard');
                    }
                }
            }
        }

        if ($type === 'sub') {
            $record = DB::table('sub_user_tokens')
                ->where('token', $token)
                ->where('expires_at', '>', now())
                ->first();

            if ($record) {
                $sub = SubUser::find($record->sub_user_id);
                if ($sub) {
                    Auth::guard('sub')->login($sub, true);
                    $request->session()->regenerate();

                    // Sliding expiration (opcional)
                    DB::table('sub_user_tokens')->where('id', $record->id)->update([
                        'expires_at' => now()->addDays(30),
                        'updated_at' => now(),
                    ]);

                    Cookie::queue('rememberme_token', 'sub|' . $token, 60 * 24 * 30);

                    if ($request->is('login')) {
                        return redirect()->route('dashboard');
                    }
                }
            }
        }


        // Token inválido o expirado → borrar cookie
        Cookie::queue(Cookie::forget('rememberme_token'));
        return $next($request);
    }
}
