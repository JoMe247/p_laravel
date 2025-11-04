<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

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
    try {
        // Validación
        $request->validate([
            'username' => 'required|string|max:50|unique:users,username',
            'name'     => 'required|string|max:100',
            'email'    => 'required|email|max:100|unique:users,email',
            'password' => 'required|string|min:8',
        ]);

        // 🔢 Obtener el último código agency
        $lastAgency = User::orderBy('id', 'desc')->value('agency');

        if ($lastAgency) {
            // Extraer el número (ejemplo: DOC-00012 → 12)
            $num = (int) str_replace('DOC-', '', $lastAgency);
            $nextNumber = $num + 1;
        } else {
            $nextNumber = 1;
        }

        // Formatear el nuevo código
        $agencyCode = 'DOC-' . str_pad($nextNumber, 5, '0', STR_PAD_LEFT);

        // Generar token
        $verificationToken = Str::random(32);

        // Crear usuario
        $user = User::create([
            'agency'             => $agencyCode, // 👈 Código generado
            'username'           => $request->username,
            'name'               => $request->name,
            'email'              => $request->email,
            'password_hash'      => Hash::make($request->password),
            'verification_token' => $verificationToken,
            'email_verified'     => 0,
            'role'               => 'user',
        ]);

        // URL de verificación
        $verificationUrl = url("/verify-email?token={$verificationToken}");

        // Enviar correo HTML
        Mail::send('emails.verify', [
            'user' => $user,
            'verificationUrl' => $verificationUrl
        ], function ($message) use ($user) {
            $message->to($user->email)
                ->subject('Verify Your Email Address');
        });

        return response()->json([
            'success' => true,
            'message' => 'Registration successful! Check your email for the verification link.',
        ]);
    } catch (\Illuminate\Validation\ValidationException $e) {
        return response()->json([
            'success' => false,
            'message' => $e->validator->errors()->first(),
        ], 422);
    } catch (\Throwable $e) {
        Log::error('Error en registro: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Internal server error: ' . $e->getMessage(),
        ], 500);
    }
}


    /**
     * Verificar email (limpiar token y marcar como verificado)
     */
    public function verifyEmail(Request $request)
    {
        $token = $request->query('token');

        if (!$token) {
            return redirect()->route('login')->with('status_error', 'Invalid verification link.');
        }

        $user = User::where('verification_token', $token)->first();

        if (!$user) {
            return redirect()->route('login')->with('status_error', 'Invalid or expired verification link.');
        }

        // Marcar como verificado y limpiar token (usando created_at como “marca” como pediste)
        $user->email_verified = 1;
        $user->verification_token = null;
        $user->created_at = now();
        $user->save();

        return redirect()->route('login')->with('status', 'Email verified successfully! You can now log in.');
    }
}
