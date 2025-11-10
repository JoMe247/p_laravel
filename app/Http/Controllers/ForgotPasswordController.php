<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Models\User;
use App\Models\SubUser;

class ForgotPasswordController extends Controller
{
    // Vista: solicitar correo
    public function showResetForm()
    {
        return view('reset');
    }

    // Simulación de envío de enlace
    public function sendResetLink(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $user = User::where('email', $request->email)->first() ??
                SubUser::where('email', $request->email)->first();

        if (!$user) {
            return back()->with('status_error', 'El correo no está registrado.');
        }

        $token = Str::random(64);

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $request->email],
            ['token' => Hash::make($token), 'created_at' => Carbon::now()]
        );

        // Simulación del enlace (ver log)
        $link = url('/new-password/' . $token);
        Log::info("🔗 Enlace de recuperación enviado a {$request->email}: {$link}");

        return back()->with('status', 'Se ha enviado un enlace de recuperación (ver log).');
    }

    // Vista: formulario para nueva contraseña
    public function showNewPassForm($token)
    {
        return view('new_pass', ['token' => $token]);
    }

    // Actualizar la contraseña
    public function updatePassword(Request $request)
    {
        $request->validate([
            'password' => 'required|min:8|confirmed',
            'token' => 'required'
        ]);

        // Buscar email asociado al token
        $records = DB::table('password_reset_tokens')->get();

        $email = null;
        foreach ($records as $rec) {
            if (Hash::check($request->token, $rec->token)) {
                $email = $rec->email;
                break;
            }
        }

        if (!$email) {
            return back()->with('status_error', 'Token inválido o expirado.');
        }

        // Buscar si es User o SubUser
        $isUser = User::where('email', $email)->exists();
        $isSubUser = SubUser::where('email', $email)->exists();

        if (!$isUser && !$isSubUser) {
            return back()->with('status_error', 'Usuario no encontrado.');
        }

        // Encriptar la nueva contraseña
        $newPasswordHash = Hash::make($request->password);

        // Actualizar según el tipo
        if ($isUser) {
            DB::table('users')->where('email', $email)->update(['password_hash' => $newPasswordHash]);
        } elseif ($isSubUser) {
            DB::table('sub_users')->where('email', $email)->update(['password_hash' => $newPasswordHash]);
        }

        // Eliminar token
        DB::table('password_reset_tokens')->where('email', $email)->delete();

        // Redirigir al login
        return redirect('/login')->with('status', 'Tu contraseña se ha restablecido correctamente.');
    }
}
