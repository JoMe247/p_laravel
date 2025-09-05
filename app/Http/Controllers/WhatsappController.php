<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Services\TwilioService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class WhatsappController extends Controller
{
    public function __construct(private TwilioService $twilio) {}


    /*public function showSendForm()
    {
        return view('send');
    }*/

    // Acción: enviar mensaje
    public function sendMessage(Request $request)
    {
        $data = $request->validate([
            'to'   => ['required', 'string'], // incluye el prefijo país, p.ej. +52...
            'body' => ['required', 'string', 'max:1000'],
        ]);

        $client = $this->twilio->client();
        $from   = $this->twilio->fromNumber();

        // Importante: destino con prefijo whatsapp:
        $to = str_starts_with($data['to'], 'whatsapp:') ? $data['to'] : 'whatsapp:' . $data['to'];

        $twMsg = $client->messages->create($to, [
            'from' => $from,
            'body' => $data['body'],
        ]);

        // Guardamos el outbound para tener historial local también
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

        return back()->with('ok', 'Mensaje enviado. SID: ' . $twMsg->sid);
    }

    // VISTA: Inbox (lee de tu BD local y permite sincronizar)
    public function showInbox(Request $request)
    {
        $messages = Message::where('direction', 'inbound')
            ->orderByDesc('date_sent')
            ->orderByDesc('id')
            ->paginate(20);

        return view('inbox', compact('messages'));
    }

    // Acción: sincronizar DESDE la API de Twilio (sin webhook)
    public function syncFromTwilio(Request $request)
    {
        $client = $this->twilio->client();
        $toNumber = $this->twilio->fromNumber(); // Tus entrantes llegan a TU número


        $after = now()->subDays(7);

        // Twilio PHP: read permite filtros; aquí pedimos muchos (p.ej. 500)
        $twilioMessages = $client->messages->read([
            'to' => $toNumber,
            // Nota: algunos SDKs aceptan 'dateSentAfter'. Si no, filtramos luego en PHP.
        ], 500);

        $countNew = 0;

        foreach ($twilioMessages as $m) {
            // Consideramos "inbound" (mensajes que te escriben)
            $isInbound = ($m->direction === 'inbound') || ($m->from && $m->to === $toNumber);

            // Filtrar por fecha local si se requiere
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

        return back()->with('ok', "Sincronización completa. Nuevos mensajes: $countNew");
    }

    // Acción: eliminar un mensaje del historial

    public function delete($id)
    {
        Message::findOrFail($id)->delete();
        return redirect()->route('inbox')->with('success', 'Mensaje eliminado');
    }

    public function deleteMultiple(Request $request)
    {
        if ($request->has('messages')) {
            Message::whereIn('id', $request->messages)->delete();
        }
        return redirect()->route('inbox')->with('success', 'Mensajes eliminados');
    }

    //envio de mensaje

    public function showSend(Request $request)
    {
        $to = $request->query('to'); // capturar número del inbox
        return view('send', compact('to'));
    }

    // VISTA: Enviados
    public function showSent(Request $request)
    {
        // Solo mensajes outbound
        $messages = Message::where('direction', 'outbound-api')
            ->orderByDesc('date_sent')
            ->orderByDesc('id')
            ->paginate(20);

        return view('sent', compact('messages'));
    }
}
