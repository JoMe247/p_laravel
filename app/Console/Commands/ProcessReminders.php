<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Reminder;
use Illuminate\Support\Facades\Log;

class ProcessReminders extends Command
{
    protected $signature = 'reminders:process';
    protected $description = 'Process reminders and simulate email sending';

    public function handle()
    {
        $now = now();

        $reminders = Reminder::where('send_email', 1)
            ->whereNull('notified_at')
            ->where('remind_at', '<=', $now)
            ->get();

        foreach ($reminders as $r) {
            Log::info('Recordatorio enviado (simulado)', [
                'reminder_id' => $r->id,
                'customer_id' => $r->customer_id,
                'remind_at'   => $r->remind_at,
                'sent_at'     => $now->toDateTimeString(),
            ]);

            $r->update([
                'notified_at' => $now
                
            ]);
        }

        return Command::SUCCESS;
    }
}
