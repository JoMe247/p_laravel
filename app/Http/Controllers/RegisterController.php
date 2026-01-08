<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Twilio\Rest\Client;


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

        // 🔢 Generar o recuperar código de agencia
        $lastAgency = User::orderBy('id', 'desc')->value('agency');

        if ($lastAgency) {
            $num = (int) str_replace('DOC-', '', $lastAgency);
            $nextNumber = $num + 1;
        } else {
            $nextNumber = 1;
        }

        $agencyCode = 'DOC-' . str_pad($nextNumber, 5, '0', STR_PAD_LEFT);

        // Token de verificación
        $verificationToken = Str::random(32);

        // ⚙️ Conectar con Twilio
        $sid    = env('TWILIO_ACCOUNT_SID');
        $token  = env('TWILIO_AUTH_TOKEN');
        $client = new Client($sid, $token);

        // 📋 Buscar si ya hay usuarios en la misma agency
        $existingAgency = User::where('agency', $agencyCode)->first();

        if (!$existingAgency) {
            // 🔍 Es una nueva agency → asignar número Twilio libre
            $numbers = $client->incomingPhoneNumbers->read([], 20);
            $assigned = User::pluck('twilio_number')->toArray();
            $availableNumber = null;

            foreach ($numbers as $num) {
                $phone = $num->phoneNumber;
                if (!in_array($phone, $assigned)) {
                    $availableNumber = $phone;
                    break;
                }
            }

            if (!$availableNumber) {
                return response()->json([
                    'success' => false,
                    'message' => 'No hay números de Twilio disponibles para asignar.',
                ], 400);
            }

            $twilioNumber = $availableNumber;
        } else {
            // 🔁 La agency ya existe → heredar número Twilio
            $twilioNumber = $existingAgency->twilio_number;
        }

        // 🧠 Crear el nuevo usuario
        $user = User::create([
            'agency'             => $agencyCode,
            'username'           => $request->username,
            'name'               => $request->name,
            'email'              => $request->email,
            'password_hash'      => Hash::make($request->password),
            'verification_token' => $verificationToken,
            'email_verified'     => 0,
            'role'               => 'user',
            'twilio_number'      => $twilioNumber,
        ]);

        // 📧 Enviar correo de verificación
        $verificationUrl = url("/verify-email?token={$verificationToken}");
        Mail::send('emails.verify', [
            'user' => $user,
            'verificationUrl' => $verificationUrl
        ], function ($message) use ($user) {
            $message->to($user->email)
                ->subject('Verify Your Email Address');
        });

        return response()->json([
            'success' => true,
            'message' => 'Usuario registrado correctamente. Verifica tu correo.',
            'agency'  => $agencyCode,
            'twilio_number' => $twilioNumber,
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
            'message' => 'Error interno: ' . $e->getMessage(),
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
