<?php

namespace App\Http\Controllers;

use App\Models\SubUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SubUserController extends Controller
{
    /**
     * Mostrar la vista de registro de sub users
     */
    public function create()
    {
        $creator = auth('web')->user(); // usuario principal
        $agency  = $creator->agency ?? null;
        $twilio  = $creator->twilio_number ?? null;

        return view('office', compact('agency', 'twilio'));
    }

    /**
     * Registrar un nuevo sub user
     */
    public function store(Request $request)
    {
        try {
            // ✅ Validación de campos
            $request->validate([
                'username' => 'required|string|max:50|unique:sub_users,username',
                'name'     => 'required|string|max:100',
                'email'    => 'required|email|max:100|unique:sub_users,email',
                'password' => 'required|string|min:8',
            ]);

            // Usuario principal autenticado (el creador)
            $creator = auth('web')->user();

            if (!$creator) {
                return back()->with('error', 'Debes iniciar sesión como usuario principal.');
            }

            // Heredar código de agencia y número Twilio
            $agency = $creator->agency;
            $twilio = $creator->twilio_number;

            // Generar token de verificación
            $verificationToken = Str::random(32);

            // 🧠 Crear sub usuario
            $sub = SubUser::create([
                'username'           => $request->username,
                'name'               => $request->name,
                'email'              => $request->email,
                'password_hash'      => Hash::make($request->password),
                'agency'             => $agency,
                'twilio_number'      => $twilio,
                'verification_token' => $verificationToken,
                'email_verified'     => 0,
            ]);

            // 🔹 Simulación de correo en el log (sin enviar email real)
            $verificationUrl = url("/verify-subuser-email?token={$verificationToken}");

            Log::debug("📨 Simulated Email Sent:");
            Log::debug("From: CRM <hello@example.com>");
            Log::debug("To: {$sub->email}");
            Log::debug("Subject: Verify Your Email Address (SubUser)");
            Log::debug("Body: Hola {$sub->name}, por favor verifica tu correo haciendo clic en el siguiente enlace:");
            Log::debug("Verification Link: {$verificationUrl}");

            return redirect()
                ->route('office.create')
                ->with('success', 'Sub-usuario creado correctamente. Verifica el log para el correo simulado.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->with('error', $e->validator->errors()->first());
        } catch (\Throwable $e) {
            Log::error('Error al registrar SubUser: ' . $e->getMessage());
            return back()->with('error', 'Hubo un error al registrar el sub-usuario.');
        }
    }

    /**
     * Verificar el correo del sub user
     */
    public function verifyEmail(Request $request)
    {
        $token = $request->query('token');

        if (!$token) {
            return redirect()->route('login')->with('error', 'Link de verificación inválido.');
        }

        $subUser = SubUser::where('verification_token', $token)->first();

        if (!$subUser) {
            return redirect()->route('login')->with('error', 'Token inválido o expirado.');
        }

        $subUser->email_verified = 1;
        $subUser->verification_token = null;
        $subUser->created_at = now();
        $subUser->save();

        return redirect()->route('login')->with('success', 'Correo verificado correctamente. Ya puedes iniciar sesión.');
    }
}
