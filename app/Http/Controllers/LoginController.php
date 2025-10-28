<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

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
        $user = User::where('username', $usernameOrEmail)
            ->orWhere('email', $usernameOrEmail)
            ->first();

        if (!$user) {
            return back()->withErrors(['username_or_email' => 'Usuario o correo no encontrado.'])->withInput();
        }

        // Verificar contraseña (usa password_hash de tu tabla)
        if (!Hash::check($password, $user->password_hash)) {
            return back()->withErrors(['password' => 'Contraseña incorrecta.'])->withInput();
        }

        // Verificar email confirmado
        if (!$user->email_verified) {
            return back()->withErrors(['email_verified' => 'Por favor verifica tu correo antes de iniciar sesión.']);
        }

        // 💡 Comprobación de sesión duplicada
        if ($user->current_session_token && session()->has('session_token') === false) {
            return back()->withErrors(['duplicate' => 'Este usuario ya tiene una sesión activa.'])->withInput();
        }

        // Generar token único para esta sesión
        $sessionToken = bin2hex(random_bytes(16));

        // Guardar token de sesión en base de datos
        $user->current_session_token = $sessionToken;
        $user->save();

        // Guardar token de sesión en la sesión de Laravel
        session(['session_token' => $sessionToken]);

        // Iniciar sesión manualmente
        Auth::login($user, $rememberMe);

        // Si el usuario marcó "Recordarme", guardar token adicional
        if ($rememberMe) {
            $token = bin2hex(random_bytes(16));

            DB::table('user_tokens')->insert([
                'user_id' => $user->id,
                'token' => $token,
                'expires_at' => now()->addDays(30),
            ]);

            // Crear cookie segura
            Cookie::queue('rememberme_token', $token, 60 * 24 * 30); // 30 días
        }

        return redirect()->route('dashboard');
    }

public function logout(Request $request)
{
    $user = Auth::user();

    if ($user) {
        // Limpia el token de sesión activo en la base de datos
        User::where('id', $user->id)->update(['current_session_token' => null]);
    }

    // Cierra la sesión de Laravel
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();

    // Borra la cookie de "remember me"
    Cookie::queue(Cookie::forget('rememberme_token'));

    return redirect()->route('login');
}

}
