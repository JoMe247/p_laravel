<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SmsMessage;
use App\Models\User;
use Twilio\Rest\Client;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class SmsController extends Controller
{
    protected $twilioSid;
    protected $twilioToken;

    public function __construct()
    {
        $this->twilioSid = config('services.twilio.sid') ?: env('TWILIO_ACCOUNT_SID');
        $this->twilioToken = config('services.twilio.token') ?: env('TWILIO_AUTH_TOKEN');
    }

    // 📩 Vista principal (inbox)
    public function index(Request $request)
    {
        $twilio = $this->getAgencyTwilioNumber(); // 🔹 NUEVO

        $list = $this->buildInboxList($twilio);

        $froms = SmsMessage::where('from', '!=', $twilio)->whereNull('deleted')->pluck('from')->toArray();
        $tos   = SmsMessage::where('to', '!=', $twilio)->whereNull('deleted')->pluck('to')->toArray();

        $contacts = array_values(array_unique(array_merge($froms, $tos)));

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

        usort($list, fn($a, $b) => strtotime($b['last_at']) <=> strtotime($a['last_at']));

        if ($request->wantsJson()) {
            return response()->json([
                'contacts' => $list,
                'twilio'   => $twilio,
            ]);
        }

        return view('sms.inbox', [
            'contacts' => $list,
            'twilio' => $twilio
        ]);
    }

    // 📜 Devuelve mensajes de una conversación
    public function messages($contact)
    {
        $twilio = $this->getAgencyTwilioNumber();

        $msgs = SmsMessage::where(function ($q) use ($contact, $twilio) {
            $q->where('from', $contact)->where('to', $twilio);
        })->orWhere(function ($q) use ($contact, $twilio) {
            $q->where('from', $twilio)->where('to', $contact);
        })
            ->whereNull('deleted')
            ->orderByRaw('COALESCE(date_sent, date_created, created_at)')
            ->get();

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

        if ($latest && $latest->deleted === 'YES' && !in_array($latest, $filtered)) {
            $filtered[] = $latest;
        }

        $user = Auth::guard('web')->user() ?? Auth::guard('sub')->user();
        $userName = $user ? $user->name : '';


        foreach ($filtered as $msg) {
            // Si el mensaje fue enviado desde el Twilio de la agencia
            if ($msg->from === $twilio) {
                $msg->sender_name = $msg->sent_by_name ?? 'Agente';
            } else {
                $msg->sender_name = 'Cliente';
            }
        }


        return response()->json($filtered);
    }

    // 🔄 Sincronización con Twilio
    public function sync(Request $request)
    {
        $twilio = $this->getAgencyTwilioNumber(); // 🔹 NUEVO
        $client = new Client($this->twilioSid, $this->twilioToken);

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

        $list = $this->buildInboxList($twilio);

        return response()->json([
            'synced'   => $count,
            'contacts' => $list,
        ]);
    }

    // 📤 Enviar SMS
    public function send(Request $request)
    {
        $request->validate([
            'to' => 'required|string',
            'body' => 'required|string'
        ]);

        // ===============================
// 1. Datos de usuario y agencia
// ===============================
$user = Auth::guard('web')->user() ?? Auth::guard('sub')->user();
$agencyCode = $user->agency;

// Obtener agency
$agency = DB::table('agency')->where('agency_code', $agencyCode)->first();

if (!$agency) {
    return response()->json([
        'ok' => false,
        'error' => 'No se encontró la agencia vinculada al usuario.'
    ], 400);
}

// Obtener número Twilio real de la agency
$twilioNumber = $this->getAgencyTwilioNumber(); // ya existente en tu controlador

// ===============================
// 2. Obtener plan desde BD doc_config
// ===============================
$plan = DB::connection('doc_config')
    ->table('limits')
    ->where('account_type', $agency->account_type)
    ->first();

if (!$plan) {
    return response()->json([
        'ok' => false,
        'error' => 'No se encontró el plan asignado a la agency.'
    ], 400);
}

$smsLimit = (int) $plan->msg_limit;

// ===============================
// 3. Contar mensajes enviados en el mes
// ===============================
$startMonth = Carbon::now()->startOfMonth();
$endMonth   = Carbon::now()->endOfMonth();

$monthlySmsCount = DB::table('sms')
    ->where('from', $twilioNumber)
    ->where('direction', 'outbound-api')
    ->whereBetween('created_at', [$startMonth, $endMonth])
    ->count();

// ===============================
// 4. Validar límite
// ===============================
if ($monthlySmsCount >= $smsLimit) {
    return response()->json([
        'ok' => false,
        'limit_error' => true,
        'message' => 'Has alcanzado tu límite mensual de mensajes. Cambia a un plan mayor.'
    ], 403);
}


        $twilio = $this->getAgencyTwilioNumber();
        $client = new Client($this->twilioSid, $this->twilioToken);

        // Enviar mensaje mediante Twilio
        $message = $client->messages->create($request->to, [
            'from' => $twilio,
            'body' => $request->body,
        ]);

        // Obtener el usuario autenticado (ya sea user o sub_user)
        $user = Auth::guard('web')->user() ?? Auth::guard('sub')->user();

        // Guardar mensaje en la tabla 'sms'
        DB::table('sms')->updateOrInsert(
            ['sid' => $message->sid],
            [
                'from' => $message->from ?? $twilio,
                'to' => $message->to ?? $request->to,
                'body' => $message->body ?? $request->body,
                'sent_by_id' => $user->id ?? null,
                'sent_by_name' => $user->name ?? $user->username ?? 'Usuario',
                'direction' => $message->direction ?? 'outbound-api',
                'status' => $message->status ?? null,
                'num_media' => intval($message->numMedia ?? 0),
                'media_urls' => json_encode([]),
                'date_sent' => isset($message->dateSent)
                    ? Carbon::parse($message->dateSent)->setTimezone('America/Mexico_City')
                    : now(),
                'date_created' => now(),
                'deleted' => null,
                
            ]
        );

        return response()->json([
            'ok' => true,
            'sid' => $message->sid,
        ]);
    }


    // 🗑️ Eliminar conversación
    public function deleteOne($contact)
    {
        $twilio = $this->getAgencyTwilioNumber(); // 🔹 NUEVO

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
        $twilio = $this->getAgencyTwilioNumber(); // 🔹 NUEVO

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

    // 🔍 Búsqueda global (sin cambios)
    public function search(Request $request)
    {
        $q = trim((string) $request->query('q', ''));

        if ($q === '') {
            return response()->json([]);
        }

        $results = SmsMessage::whereNull('deleted')
            ->where(function ($qBuilder) use ($q) {
                $qBuilder->where('body', 'LIKE', "%{$q}%")
                    ->orWhere('from', 'LIKE', "%{$q}%")
                    ->orWhere('to', 'LIKE', "%{$q}%");
            })
            ->orderByRaw('COALESCE(date_sent, date_created, created_at) DESC')
            ->limit(200)
            ->get(['id', 'from', 'to', 'body', 'date_sent', 'date_created', 'created_at']);

        return response()->json($results);
    }

    // 🔹 NUEVO método para obtener número Twilio según agency
    private function getAgencyTwilioNumber()
    {
        // Buscar usuario autenticado en cualquier guard
        $user = Auth::guard('web')->user() ?? Auth::guard('sub')->user();

        if (!$user) {
            throw new \Exception('Usuario no autenticado');
        }

        // Si el usuario es sub_user, su agency es la misma del usuario principal
        $agency = $user->agency ?? null;

        // Buscar al usuario principal (de la tabla users) con la misma agencia
        $agencyUser = User::where('agency', $agency)->first();

        if (!$agencyUser || !$agencyUser->twilio_number) {
            throw new \Exception('No se encontró número Twilio asignado para esta agencia');
        }

        return $agencyUser->twilio_number;
    }


    // Método auxiliar (sin cambios)
    private function buildInboxList(string $twilio): array
    {
        $froms = SmsMessage::where('from', '!=', $twilio)->whereNull('deleted')->pluck('from')->toArray();
        $tos   = SmsMessage::where('to', '!=', $twilio)->whereNull('deleted')->pluck('to')->toArray();
        $contacts = array_values(array_unique(array_merge($froms, $tos)));

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
                    'contact'   => $c,
                    'last_body' => $last->body,
                    'last_at'   => $last->date_sent,
                ];
            }
        }

        usort($list, fn($a, $b) => strtotime($b['last_at']) <=> strtotime($a['last_at']));
        return $list;
    }
}
