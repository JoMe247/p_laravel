<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\SubUser;


class LoginController extends Controller
{
    public function show()
    {
        return view('login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'username_or_email' => 'required|string',
            'password' => 'required|string',
        ]);

        DB::table('user_tokens')->where('expires_at', '<', now())->delete();

        $usernameOrEmail = trim($request->input('username_or_email'));
        $password = $request->input('password');
        $rememberMe = $request->has('remember_me');

        // Buscar usuario por username o email
        // Buscar primero en users
        $user = User::where('username', $usernameOrEmail)
            ->orWhere('email', $usernameOrEmail)
            ->first();

        $isSubUser = false;

        // Si no existe, buscar en sub_users
        if (!$user) {
            $user = SubUser::where('username', $usernameOrEmail)
                ->orWhere('email', $usernameOrEmail)
                ->first();

            if ($user) {
                $isSubUser = true;
            } else {
                return back()->withErrors(['username_or_email' => 'Usuario o correo no encontrado.'])->withInput();
            }
        }

        // Verificar contraseña
        if (!Hash::check($password, $user->password_hash)) {
            return back()->withErrors(['password' => 'Contraseña incorrecta.'])->withInput();
        }

        // Verificar email confirmado
        if (!$user->email_verified) {
            return back()->withErrors(['email_verified' => 'Por favor verifica tu correo antes de iniciar sesión.']);
        }

        // 🔐 Cerrar sesión anterior si existía
        if ($user->current_session_token) {
            if ($isSubUser) {
                SubUser::where('id', $user->id)->update(['current_session_token' => null]);
            } else {
                User::where('id', $user->id)->update(['current_session_token' => null]);
            }
        }

        // Generar nuevo token de sesión
        $sessionToken = bin2hex(random_bytes(16));
        $user->current_session_token = $sessionToken;
        $user->save();

        // Guardar token en sesión
        session(['session_token' => $sessionToken]);

        // Iniciar sesión
        if ($isSubUser) {
            Auth::guard('sub')->login($user, false);
            session(['auth_guard' => 'sub']);
        } else {
            Auth::guard('web')->login($user, false);
            session(['auth_guard' => 'web']);
        }

        // Si el usuario marcó "Recordarme"
        if ($rememberMe) {
            $token = bin2hex(random_bytes(32));

            if ($isSubUser) {
                DB::table('sub_user_tokens')->insert([
                    'sub_user_id' => $user->id,
                    'token'       => $token,
                    'expires_at'  => now()->addDays(30),
                ]);

                Cookie::queue('rememberme_token', 'sub|' . $token, 60 * 24 * 30);
            } else {
                DB::table('user_tokens')->insert([
                    'user_id'    => $user->id,
                    'token'      => $token,
                    'expires_at' => now()->addDays(30),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                Cookie::queue('rememberme_token', 'web|' . $token, 60 * 24 * 30);
            }
        }



        return redirect()->route('dashboard');
    }

    public function logout(Request $request)
    {
        // Detectar guard activo
        $guard = session('auth_guard') ?? null;

        // Determinar el usuario actual según el guard
        $user = null;
        if ($guard === 'sub') {
            $user = Auth::guard('sub')->user();
        } else {
            $user = Auth::guard('web')->user();
        }

        if ($user) {
            if ($guard === 'sub') {
                // 🧹 Limpiar token de sesión para sub_users
                \App\Models\SubUser::where('id', $user->id)->update(['current_session_token' => null]);

                // ✅ BORRAR tokens remember del sub user (tabla correcta)
                DB::table('sub_user_tokens')->where('sub_user_id', $user->id)->delete();
            } else {
                // 🧹 Limpiar token de sesión para users
                DB::table('user_tokens')->where('user_id', $user->id)->delete();
                \App\Models\User::where('id', $user->id)->update(['current_session_token' => null]);
            }
        }

        // Cerrar sesión del guard correcto
        if ($guard === 'sub') {
            Auth::guard('sub')->logout();
        } else {
            Auth::guard('web')->logout();
        }

        // Invalidar sesión y regenerar token CSRF
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // ✅ Borrar cookies remember_* de Laravel (recaller) SIN getRecallerName()
        foreach ($request->cookies->all() as $name => $value) {
            if (str_starts_with($name, 'remember_')) {
                Cookie::queue(Cookie::forget($name));
            }
        }

        // ✅ Borrar tu cookie custom SIEMPRE (user y sub)
        Cookie::queue(Cookie::forget('rememberme_token'));

        // Limpiar el valor del guard activo en la sesión
        session()->forget('auth_guard');

        // Redirigir al login
        return redirect()->route('login');
    }
}
