<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SmsMessage;
use Twilio\Rest\Client;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class SmsController extends Controller
{
    protected $twilioSid;
    protected $twilioToken;
    protected $twilioFrom;

    public function __construct()
    {
        $this->twilioSid = config('services.twilio.sid') ?: env('TWILIO_ACCOUNT_SID');
        $this->twilioToken = config('services.twilio.token') ?: env('TWILIO_AUTH_TOKEN');
        $this->twilioFrom = config('services.twilio.from') ?: env('TWILIO_SMS_FROM');
    }

    // 📩 Vista principal (inbox)
    public function index()
    {
        $twilio = $this->twilioFrom;

        // Números de contactos con mensajes no eliminados
        $froms = SmsMessage::where('from', '!=', $twilio)->whereNull('deleted')->pluck('from')->toArray();
        $tos   = SmsMessage::where('to', '!=', $twilio)->whereNull('deleted')->pluck('to')->toArray();

        $contacts = array_values(array_unique(array_merge($froms, $tos)));

        // Lista con último mensaje por contacto
        $list = [];
        foreach ($contacts as $c) {
            $last = SmsMessage::where(function ($q) use ($c, $twilio) {
                $q->where('from', $c)->where('to', $twilio);
            })->orWhere(function ($q) use ($c, $twilio) {
                $q->where('from', $twilio)->where('to', $c);
            })
                ->whereNull('deleted')
                ->orderBy('date_sent', 'desc')
                ->first();

            if ($last) {
                $list[] = [
                    'contact' => $c,
                    'last_body' => $last->body,
                    'last_at' => $last->date_sent,
                ];
            }
        }

        // Ordenar por último mensaje (descendente)
        usort($list, fn($a, $b) => strtotime($b['last_at']) <=> strtotime($a['last_at']));

        return view('sms.inbox', [
            'contacts' => $list,
            'twilio' => $twilio
        ]);
    }

    // 📜 Devuelve mensajes de una conversación
    public function messages($contact)
    {
        $twilio = $this->twilioFrom;

        $msgs = SmsMessage::where(function ($q) use ($contact, $twilio) {
            $q->where('from', $contact)->where('to', $twilio);
        })->orWhere(function ($q) use ($contact, $twilio) {
            $q->where('from', $twilio)->where('to', $contact);
        })
            ->whereNull('deleted')
            ->orderByRaw('COALESCE(date_sent, date_created, created_at)')
            ->get();

        // Filtrar mensajes eliminados
        $filtered = [];
        $latest = null;
        foreach ($msgs as $m) {
            if ($m->deleted !== 'YES') {
                $filtered[] = $m;
            }
            if (!$latest || $m->date_sent > $latest->date_sent) {
                $latest = $m;
            }
        }

        // Si el último mensaje fue eliminado, mostrar solo ese
        if ($latest && $latest->deleted === 'YES' && !in_array($latest, $filtered)) {
            $filtered[] = $latest;
        }

        return response()->json($filtered);
    }

    // 🔄 Sincronización con Twilio
    public function sync(Request $request)
    {
        $client = new Client($this->twilioSid, $this->twilioToken);
        $twilio = $this->twilioFrom;

        $inbound = $client->messages->read(['to' => $twilio], 200);
        $outbound = $client->messages->read(['from' => $twilio], 200);
        $all = array_merge($inbound, $outbound);

        $count = 0;
        foreach ($all as $m) {
            $sid = $m->sid;
            $numMedia = intval($m->numMedia ?? 0);
            $mediaUrls = [];

            for ($i = 0; $i < $numMedia; $i++) {
                if (isset($m->{"mediaUrl" . $i})) {
                    $mediaUrls[] = $m->{"mediaUrl" . $i};
                }
            }

            SmsMessage::updateOrCreate(
                ['sid' => $sid],
                [
                    'from' => $m->from ?? '',
                    'to' => $m->to ?? '',
                    'body' => $m->body ?? '',
                    'direction' => $m->direction ?? '',
                    'status' => $m->status ?? '',
                    'num_media' => $numMedia,
                    'media_urls' => $mediaUrls ?: null,
                    'date_sent' => isset($m->dateSent) ? Carbon::parse($m->dateSent)->setTimezone('America/Mexico_City') : now(),
                    'date_created' => isset($m->dateCreated) ? Carbon::parse($m->dateCreated)->setTimezone('America/Mexico_City') : now(),
                    'deleted' => DB::raw('IF(deleted="YES", "YES", NULL)')
                ]
            );

            $count++;
        }

        return response()->json(['synced' => $count]);
    }

    // 📤 Enviar SMS
    public function send(Request $request)
    {
        $request->validate([
            'to' => 'required|string',
            'body' => 'required|string'
        ]);

        $to = $request->input('to');
        $body = $request->input('body');

        $client = new Client($this->twilioSid, $this->twilioToken);
        $message = $client->messages->create($to, [
            'from' => $this->twilioFrom,
            'body' => $body,
        ]);

        SmsMessage::updateOrCreate(
            ['sid' => $message->sid],
            [
                'from' => $message->from ?? $this->twilioFrom,
                'to' => $message->to ?? $to,
                'body' => $message->body ?? $body,
                'direction' => $message->direction ?? 'outbound-api',
                'status' => $message->status ?? null,
                'num_media' => intval($message->numMedia ?? 0),
                'media_urls' => [],
                'date_sent' => isset($message->dateSent) ? Carbon::parse($message->dateSent)->setTimezone('America/Mexico_City') : now(),
                'date_created' => now(),
                'deleted' => null
            ]
        );

        return response()->json(['ok' => true, 'sid' => $message->sid]);
    }

    // 🗑️ Eliminar conversación (marcar deleted=YES)
    public function deleteOne($contact)
    {
        $twilio = $this->twilioFrom;

        $updated = SmsMessage::where(function ($q) use ($contact, $twilio) {
            $q->where('from', $contact)->where('to', $twilio);
        })->orWhere(function ($q) use ($contact, $twilio) {
            $q->where('from', $twilio)->where('to', $contact);
        })->update(['deleted' => 'YES']);

        return response()->json([
            'success' => true,
            'deleted_count' => $updated,
            'contact' => $contact,
            'message' => 'Conversación eliminada correctamente'
        ]);
    }

    // 🗑️ Eliminar múltiples conversaciones
    public function deleteMany(Request $request)
    {
        $contacts = $request->contacts ?? [];
        $twilio = $this->twilioFrom;

        if (empty($contacts)) {
            return response()->json([
                'success' => false,
                'deleted_count' => 0,
                'message' => 'No se seleccionaron conversaciones'
            ], 400);
        }

        $updated = SmsMessage::where(function ($q) use ($contacts, $twilio) {
            $q->whereIn('from', $contacts)->where('to', $twilio);
        })->orWhere(function ($q) use ($contacts, $twilio) {
            $q->where('from', $twilio)->whereIn('to', $contacts);
        })->update(['deleted' => 'YES']);

        return response()->json([
            'success' => true,
            'deleted_count' => $updated,
            'contacts' => $contacts,
            'message' => 'Conversaciones eliminadas correctamente'
        ]);
    }
}
