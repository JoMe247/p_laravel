<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\TwilioService;
use App\Models\Message;
use Illuminate\Support\Carbon;
use Illuminate\Console\Scheduling\Schedule;

class SyncTwilioMessages extends Command
{
    protected $signature = 'twilio:sync {--days=1} {--limit=500}';
    protected $description = 'Sincroniza mensajes inbound desde Twilio sin webhook';

    public function handle(TwilioService $twilio)
    {
        $client = $twilio->client();
        $toNumber = $twilio->fromNumber();
        $after = now()->subDays((int)$this->option('days'));
        $limit = (int)$this->option('limit');

        $msgs = $client->messages->read(['to' => $toNumber], $limit);

        $new = 0;
        foreach ($msgs as $m) {
            $dateSent = $m->dateSent ? Carbon::parse($m->dateSent) : null;
            if ($dateSent && $dateSent->lt($after)) continue;

            $isInbound = ($m->direction === 'inbound') || ($m->to === $toNumber);

            if ($isInbound) {
                $created = Message::updateOrCreate(
                    ['sid' => $m->sid],
                    [
                        'from' => $m->from,
                        'to' => $m->to,
                        'body' => $m->body,
                        'direction' => $m->direction ?? 'inbound',
                        'status' => $m->status,
                        'date_sent' => $dateSent,
                        'error_code' => $m->errorCode,
                        'error_message' => $m->errorMessage,
                        'raw' => $m->toArray(),
                    ],
                );
                if ($created->wasRecentlyCreated) $new++;
            }
        }

        $this->info("Sincronizados: {$new} nuevos");
        return self::SUCCESS;
    }

    public function schedule(Schedule $schedule): void
    {
        // se ejecutará cada 5 minutos
        $schedule->command(static::class, [
            '--days' => 1,
            '--limit' => 500,
        ])->everyFiveMinutes();
    }
}
