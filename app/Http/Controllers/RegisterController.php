<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Models\User;
use Illuminate\Support\Str;

class RegisterController extends Controller
{
    // Mostrar la vista de registro
    public function show()
    {
        return view('register');
    }

    // Procesar registro
    public function register(Request $request)
    {
        $request->validate([
            'username' => 'required|string|max:50|unique:users,username',
            'email'    => 'required|email|max:100|unique:users,email',
            'password' => 'required|string|min:8',
        ]);

        $verificationToken = Str::random(32);

        $user = User::create([
            'username' => $request->username,
            'email'    => $request->email,
            'password_hash' => Hash::make($request->password),
            'verification_token' => $verificationToken,
            'email_verified' => 0,
        ]);

        $verificationUrl = route('verify.email', ['token' => $verificationToken]);

        // Correo simple de verificación
        $subject = 'Verifica tu correo electrónico';
        $message = "Hola {$user->username},\n\nPor favor haz clic en el siguiente enlace para verificar tu cuenta:\n\n{$verificationUrl}\n\nGracias.";

        Mail::raw($message, function ($mail) use ($user, $subject) {
            $mail->to($user->email)
                 ->subject($subject);
        });

        return response()->json([
            'success' => true,
            'message' => '¡Registro exitoso! Revisa tu correo para verificar tu cuenta.',
        ]);
    }

    // Verificar el correo
    public function verifyEmail($token)
    {
        $user = User::where('verification_token', $token)->first();

        if (!$user) {
            return view('verify-result', [
                'message' => 'Token inválido o expirado.',
                'success' => false
            ]);
        }

        $user->update([
            'email_verified' => 1,
            'verification_token' => null,
            'created_at' => now(), // usamos created_at como marca de verificación
        ]);

        return view('verify-result', [
            'message' => '¡Correo verificado correctamente! Ya puedes iniciar sesión.',
            'success' => true
        ]);
    }
}
