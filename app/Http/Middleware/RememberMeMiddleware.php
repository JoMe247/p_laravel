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
        if (Auth::guard('web')->check()) {
            session(['auth_guard' => 'web']);
            return $next($request);
        }
        if (Auth::guard('sub')->check()) {
            session(['auth_guard' => 'sub']);
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

        // Limpieza de expirados (recomendado)
        DB::table('user_tokens')->where('expires_at', '<', now())->delete();
        DB::table('sub_user_tokens')->where('expires_at', '<', now())->delete();

        $loggedIn = false;

        if ($type === 'web') {
            $record = DB::table('user_tokens')
                ->where('token', $token)
                ->where('expires_at', '>', now())
                ->first();

            if ($record) {
                $user = User::find($record->user_id);

                if ($user && $user->email_verified) {
                    // OJO: usa false para NO crear cookies remember_* de Laravel
                    Auth::guard('web')->login($user, false);
                    session(['auth_guard' => 'web']);

                    // Tu sistema usa session_token + current_session_token
                    $sessionToken = bin2hex(random_bytes(16));
                    $user->current_session_token = $sessionToken;
                    $user->save();
                    session(['session_token' => $sessionToken]);

                    $request->session()->regenerate();

                    // Sliding expiration
                    DB::table('user_tokens')->where('id', $record->id)->update([
                        'expires_at' => now()->addDays(30),
                        'updated_at' => now(),
                    ]);
                    Cookie::queue('rememberme_token', 'web|' . $token, 60 * 24 * 30);

                    $loggedIn = true;

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

                if ($sub && $sub->email_verified) {
                    Auth::guard('sub')->login($sub, false);
                    session(['auth_guard' => 'sub']);

                    $sessionToken = bin2hex(random_bytes(16));
                    $sub->current_session_token = $sessionToken;
                    $sub->save();
                    session(['session_token' => $sessionToken]);

                    $request->session()->regenerate();

                    DB::table('sub_user_tokens')->where('id', $record->id)->update([
                        'expires_at' => now()->addDays(30),
                        'updated_at' => now(),
                    ]);
                    Cookie::queue('rememberme_token', 'sub|' . $token, 60 * 24 * 30);

                    $loggedIn = true;

                    if ($request->is('login')) {
                        return redirect()->route('dashboard');
                    }
                }
            }
        }

        // ✅ SOLO si NO logró autenticar, borramos cookie
        if (!$loggedIn) {
            Cookie::queue(Cookie::forget('rememberme_token'));
        }

        return $next($request);
    }
}