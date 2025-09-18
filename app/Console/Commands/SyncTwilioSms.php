<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Twilio\Rest\Client;
use App\Models\SmsMessage;
use Carbon\Carbon;

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

        $inbound = $client->messages->read(['to'=>$from], 200);
        $outbound = $client->messages->read(['from'=>$from], 200);
        $all = array_merge($inbound, $outbound);

        $count = 0;
        foreach ($all as $m) {
            SmsMessage::updateOrCreate(
                ['sid' => $m->sid],
                [
                    'from' => $m->from ?? '',
                    'to' => $m->to ?? '',
                    'body' => $m->body ?? '',
                    'direction' => $m->direction ?? '',
                    'status' => $m->status ?? '',
                    'num_media' => intval($m->numMedia ?? 0),
                    'media_urls' => [],
                    'date_sent' => isset($m->dateSent) ? Carbon::parse($m->dateSent) : null,
                    'date_created' => isset($m->dateCreated) ? Carbon::parse($m->dateCreated) : null,
                ]
            );
            $count++;
        }

        $this->info("Synced {$count} messages");
    }
}
