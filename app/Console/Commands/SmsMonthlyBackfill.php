<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class SmsMonthlyBackfill extends Command
{
    protected $signature = 'sms:monthly-backfill {--month=} {--year=}';
    protected $description = 'Guarda en doc_config el conteo mensual de SMS enviados (outbound-api) por agency';

    public function handle()
    {
        // Por default: correr para el mes anterior (recomendado si corre el día 1)
        $month = (int)($this->option('month') ?? now()->subMonth()->month);
        $year  = (int)($this->option('year')  ?? now()->subMonth()->year);

        $start = Carbon::create($year, $month, 1)->startOfMonth();
        $end   = Carbon::create($year, $month, 1)->endOfMonth();

        // Tomamos todas las agencies con twilio_number (DB principal)
        $owners = DB::table('users')
            ->select('agency as agency_code', 'twilio_number')
            ->whereNotNull('agency')
            ->where('agency', '!=', '')
            ->whereNotNull('twilio_number')
            ->where('twilio_number', '!=', '')
            ->get();

        $docDb = DB::connection('doc_config');

        $processed = 0;

        foreach ($owners as $o) {
            $agencyCode   = $o->agency_code;
            $twilioNumber = $o->twilio_number;

            // Conteo SOLO outbound-api (DB principal)
            $count = DB::table('sms')
                ->where('from', $twilioNumber)
                ->where('direction', 'outbound-api')
                ->whereBetween(DB::raw('COALESCE(date_sent, date_created)'), [$start, $end])
                ->count();

            // Upsert en doc_config sin tocar created_at en updates
            $existing = $docDb->table('sms_monthly_counters')
                ->where('agency_code', $agencyCode)
                ->where('mes', $month)
                ->where('anio', $year)
                ->first();

            if ($existing) {
                $docDb->table('sms_monthly_counters')
                    ->where('id', $existing->id)
                    ->update([
                        'twilio_number' => $twilioNumber,
                        'cantidad'      => $count,
                        'updated_at'    => now(),
                    ]);
            } else {
                $docDb->table('sms_monthly_counters')
                    ->insert([
                        'agency_code'   => $agencyCode,
                        'twilio_number' => $twilioNumber,
                        'mes'           => $month,
                        'anio'          => $year,
                        'cantidad'      => $count,
                        'created_at'    => now(),
                        'updated_at'    => now(),
                    ]);
            }

            $processed++;
        }

        $this->info("OK: mes={$month}, año={$year}, agencies procesadas={$processed}");
        return Command::SUCCESS;
    }
}
