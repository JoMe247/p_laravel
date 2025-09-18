<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SmsMessage;
use Twilio\Rest\Client;
use Illuminate\Support\Facades\Config;
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

    // Vista principal (inbox)
    public function index()
    {
        // Lista de contactos (números) (coger todos los números que no sean nuestro sender)
        $twilio = $this->twilioFrom;

        // Obtener números únicos conversados
        $froms = SmsMessage::where('from', '!=', $twilio)->pluck('from')->toArray();
        $tos   = SmsMessage::where('to', '!=', $twilio)->pluck('to')->toArray();

        $contacts = array_values(array_unique(array_merge($froms, $tos)));

        // prepara lista con último mensaje por contacto
        $list = [];
        foreach ($contacts as $c) {
            $last = SmsMessage::where(function($q) use ($c,$twilio){
                $q->where('from', $c)->where('to', $twilio);
            })->orWhere(function($q) use ($c,$twilio){
                $q->where('from', $twilio)->where('to', $c);
            })->orderBy('date_sent','desc')->first();

            $list[] = [
                'contact' => $c,
                'last_body' => $last ? $last->body : '',
                'last_at' => $last ? $last->date_sent : null,
            ];
        }

        // order by last_at desc
        usort($list, function($a,$b){
            return strtotime($b['last_at']) <=> strtotime($a['last_at']);
        });

        return view('sms.inbox', [
            'contacts' => $list,
            'twilio' => $twilio
        ]);
    }

    // Devuelve mensajes de una conversación en JSON (AJAX)
    public function messages($contact)
    {
        $twilio = $this->twilioFrom;
        $msgs = SmsMessage::where(function($q) use ($contact,$twilio){
            $q->where('from', $contact)->where('to', $twilio);
        })->orWhere(function($q) use ($contact,$twilio){
            $q->where('from', $twilio)->where('to', $contact);
        })->orderBy('date_sent','asc')->get();

        return response()->json($msgs);
    }

    // Llama a la API Twilio y sincroniza mensajes en DB (leer both directions)
    public function sync(Request $request)
    {
        $client = new Client($this->twilioSid, $this->twilioToken);
        $twilio = $this->twilioFrom;

        // Leer mensajes TO Twilio (inbound - what users send to our Twilio number)
        $inbound = $client->messages->read(['to' => $twilio], 200);
        // Leer mensajes FROM Twilio (outbound - messages we have sent)
        $outbound = $client->messages->read(['from' => $twilio], 200);

        $all = array_merge($inbound, $outbound);

        $count = 0;
        foreach ($all as $m) {
            // $m es objeto MessageInstance
            $sid = $m->sid;
            // evita duplicados
            $exists = SmsMessage::where('sid', $sid)->exists();
            $mediaUrls = [];
            $numMedia = intval($m->numMedia ?? 0);
            for ($i=0; $i < $numMedia; $i++) {
                $field = "mediaUrl{$i}";
                // MessageInstance no expone MediaUrlN directamente en SDK; si lo necesitas, usa the REST endpoint or the webhook.
                // Aquí intentamos acceder desde array form (si no existe, lo dejamos vacío)
                if (isset($m->{"mediaUrl".$i})) {
                    $mediaUrls[] = $m->{"mediaUrl".$i};
                }
            }

            $data = [
                'sid' => $sid,
                'from' => $m->from ?? '',
                'to' => $m->to ?? '',
                'body' => $m->body ?? '',
                'direction' => $m->direction ?? '',
                'status' => $m->status ?? '',
                'num_media' => $numMedia,
                'media_urls' => $mediaUrls ?: null,
                'date_sent' => isset($m->dateSent) ? Carbon::parse($m->dateSent) : null,
                'date_created' => isset($m->dateCreated) ? Carbon::parse($m->dateCreated) : null,
            ];

            // Upsert por sid
            SmsMessage::updateOrCreate(['sid' => $sid], $data);
            $count++;
        }

        return response()->json(['synced' => $count]);
    }

    // Enviar SMS (respuesta o nuevo)
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

        // guarda en DB
        SmsMessage::updateOrCreate(
            ['sid'=>$message->sid],
            [
                'from' => $message->from ?? $this->twilioFrom,
                'to' => $message->to ?? $to,
                'body' => $message->body ?? $body,
                'direction' => $message->direction ?? 'outbound-api',
                'status' => $message->status ?? null,
                'num_media' => intval($message->numMedia ?? 0),
                'media_urls' => [],
                'date_sent' => isset($message->dateSent) ? Carbon::parse($message->dateSent) : now()
            ]
        );

        return response()->json(['ok'=>true,'sid'=>$message->sid]);
    }

    // Eliminar mensajes
    public function deleteOne($contact)
{
    DB::table('sms')->where('contact', $contact)->delete();
    return response()->json(['message' => 'Conversación eliminada']);
}

public function deleteMany(Request $request)
{
    $contacts = $request->contacts ?? [];
    DB::table('sms')->whereIn('contact', $contacts)->delete();
    return response()->json(['message' => 'Conversaciones eliminadas']);
}

}
