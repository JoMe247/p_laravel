<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Twilio\Rest\Client;
use App\Models\SmsMessage;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SyncTwilioSms extends Command
{
    protected $signature = 'twilio:sms-sync';
    protected $description = 'Sync SMS messages from Twilio into local DB';

    public function handle()
    {
        $sid = config('services.twilio.sid') ?: env('TWILIO_ACCOUNT_SID');
        $token = config('services.twilio.token') ?: env('TWILIO_AUTH_TOKEN');
        $from = config('services.twilio.from') ?: env('TWILIO_SMS_FROM');

        $client = new Client($sid, $token);

        // Leer mensajes inbound y outbound
        $inbound = $client->messages->read(['to' => $from], 200);
        $outbound = $client->messages->read(['from' => $from], 200);
        $all = array_merge($inbound, $outbound);

        $count = 0;
        foreach ($all as $m) {
            $sidMsg = $m->sid;
            $numMedia = intval($m->numMedia ?? 0);
            $mediaUrls = [];

            for ($i = 0; $i < $numMedia; $i++) {
                if (isset($m->{"mediaUrl" . $i})) {
                    $mediaUrls[] = $m->{"mediaUrl" . $i};
                }
            }

            // Mantener deleted=YES si ya estaba eliminado
            $existing = SmsMessage::where('sid', $sidMsg)->first();
            $deletedValue = $existing && $existing->deleted === 'YES' ? 'YES' : null;

            SmsMessage::updateOrCreate(
                ['sid' => $sidMsg],
                [
                    'from' => $m->from ?? '',
                    'to' => $m->to ?? '',
                    'body' => $m->body ?? '',
                    'direction' => $m->direction ?? '',
                    'status' => $m->status ?? '',
                    'num_media' => $numMedia,
                    'media_urls' => $mediaUrls ?: null,
                    'date_sent' => isset($m->dateSent) ? Carbon::parse($m->dateSent) : null,
                    'date_created' => isset($m->dateCreated) ? Carbon::parse($m->dateCreated) : null,
                    'deleted' => $deletedValue
                ]
            );

            $count++;
        }

        $this->info("Synced {$count} messages");
    }
}
