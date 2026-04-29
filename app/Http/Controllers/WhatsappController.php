<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Services\TwilioService;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Twilio\Rest\Client;
use Illuminate\Support\Facades\Auth;


class WhatsappController extends Controller
{
    public function __construct(private TwilioService $twilio) {}

    // Acción: enviar mensaje
    public function sendMessage(Request $request)
    {
        $data = $request->validate([
            'to'   => ['required', 'string'], // incluye el prefijo país, p.ej. +52...
            'body' => ['required', 'string', 'max:1000'],
        ]);

        // 🔹 Obtener número dinámico por agencia
        $from = $this->getAgencyWhatsappNumber();

        // Prefijo correcto para destino
        $to = str_starts_with($data['to'], 'whatsapp:') ? $data['to'] : 'whatsapp:' . $data['to'];

        // Crear cliente Twilio directamente con SID y TOKEN del .env
        $client = new Client(env('TWILIO_ACCOUNT_SID'), env('TWILIO_AUTH_TOKEN'));

        // Enviar mensaje
        $twMsg = $client->messages->create($to, [
            'from' => $from,
            'body' => $data['body'],
        ]);

        // Guardar historial
        Message::updateOrCreate(
            ['sid' => $twMsg->sid],
            [
                'from'       => $twMsg->from,
                'to'         => $twMsg->to,
                'body'       => $twMsg->body,
                'direction'  => $twMsg->direction ?? 'outbound-api',
                'status'     => $twMsg->status,
                'date_sent'  => $twMsg->dateSent ? Carbon::parse($twMsg->dateSent) : now(),
                'error_code' => $twMsg->errorCode,
                'error_message' => $twMsg->errorMessage,
                'raw'        => $twMsg->toArray(),
            ]
        );

        return back()->with('ok', 'Mensaje enviado desde ' . $from . ' (SID: ' . $twMsg->sid . ')');
    }

    // VISTA: Inbox (lee de BD local)
    public function showInbox(Request $request)
    {

        // Obtener usuario autenticado (funciona también con remember me)
        $user = Auth::guard('web')->user() ?? Auth::guard('sub')->user();

        // En caso de no estar autenticado, redirige al login
        if (!$user) {
            return redirect()->route('login');
        }
        $messages = Message::where('direction', 'inbound')
            ->orderByDesc('date_sent')
            ->orderByDesc('id')
            ->paginate(20);

        return view('whatsapp', compact('messages')); // <-- antes: view('inbox')
    }

    // Sincronizar mensajes desde Twilio según agency
    public function syncFromTwilio(Request $request)
    {
        $fromNumber = $this->getAgencyWhatsappNumber();
        $client = new Client(env('TWILIO_ACCOUNT_SID'), env('TWILIO_AUTH_TOKEN'));

        $after = now()->subDays(7);

        $twilioMessages = $client->messages->read([
            'to' => $fromNumber,
        ], 500);

        $countNew = 0;

        foreach ($twilioMessages as $m) {
            $isInbound = ($m->direction === 'inbound') || ($m->to === $fromNumber);

            $dateSent = $m->dateSent ? Carbon::parse($m->dateSent) : null;
            if ($dateSent && $dateSent->lt($after)) {
                continue;
            }

            if ($isInbound) {
                $created = Message::updateOrCreate(
                    ['sid' => $m->sid],
                    [
                        'from'       => $m->from,
                        'to'         => $m->to,
                        'body'       => $m->body,
                        'direction'  => $m->direction ?? 'inbound',
                        'status'     => $m->status,
                        'date_sent'  => $dateSent,
                        'error_code' => $m->errorCode,
                        'error_message' => $m->errorMessage,
                        'raw'        => $m->toArray(),
                    ]
                );

                if ($created->wasRecentlyCreated) {
                    $countNew++;
                }
            }
        }

        return back()->with('ok', "Sincronización completa para {$fromNumber}. Nuevos mensajes: $countNew");
    }

    // Acción: eliminar un mensaje del historial
    public function delete($id)
    {
        Message::findOrFail($id)->delete();
        return redirect()->route('whatsapp.inbox')->with('success', 'Mensaje eliminado');
    }

    public function deleteMultiple(Request $request)
    {
        if ($request->has('messages')) {
            Message::whereIn('id', $request->messages)->delete();
        }
        return redirect()->route('whatsapp.inbox')->with('success', 'Mensajes eliminados');
    }

    public function showSend(Request $request)
    {
        $to = $request->query('to');
        return view('send', compact('to'));
    }

    public function showSent(Request $request)
    {
        $messages = Message::where('direction', 'outbound-api')
            ->orderByDesc('date_sent')
            ->orderByDesc('id')
            ->paginate(20);

        return view('sent_whatsapp', compact('messages'));
    }

    // 🔹 NUEVO: obtener número Twilio de la agencia
    private function getAgencyWhatsappNumber(): string
    {
        $user = Auth::user();


        if (!$user) {
            throw new \Exception('Usuario no autenticado.');
        }

        $agencyUser = User::where('agency', $user->agency)->first();

        if (!$agencyUser || !$agencyUser->twilio_number) {
            throw new \Exception('No se encontró número de WhatsApp asignado para esta agencia.');
        }

        // Asegurar formato "whatsapp:+123..."
        $number = $agencyUser->twilio_number;
        return str_starts_with($number, 'whatsapp:') ? $number : 'whatsapp:' . $number;
    }
}
