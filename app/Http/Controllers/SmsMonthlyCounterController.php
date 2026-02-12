<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class SmsMonthlyCounterController extends Controller
{
    public function store(Request $request)
    {
        $authUser = Auth::guard('web')->user() ?? Auth::guard('sub')->user();
        if (!$authUser) return response()->json(['message' => 'No autenticado.'], 401);

        $agencyCode = $authUser->agency;
        if (!$agencyCode) return response()->json(['message' => 'El usuario no tiene agency vinculada.'], 403);

        $ownerUser = User::where('agency', $agencyCode)->first();
        if (!$ownerUser || !$ownerUser->twilio_number) {
            return response()->json(['message' => 'No se encontró número Twilio asignado para esta agency.'], 403);
        }

        $twilioNumber = $ownerUser->twilio_number;

        $month = (int) ($request->query('month') ?? Carbon::now()->month);
        $year  = (int) ($request->query('year')  ?? Carbon::now()->year);

        $start = Carbon::create($year, $month, 1)->startOfMonth();
        $end   = Carbon::create($year, $month, 1)->endOfMonth();

        $count = DB::table('sms')
            ->where('from', $twilioNumber)
            ->where('direction', 'outbound-api')
            ->whereBetween(DB::raw('COALESCE(date_sent, date_created)'), [$start, $end])
            ->count();

        $this->upsertMonthly($agencyCode, $twilioNumber, $month, $year, $count);

        return response()->json([
            'message' => 'Conteo mensual guardado en doc_config.',
            'agency_code' => $agencyCode,
            'twilio_number' => $twilioNumber,
            'mes' => $month,
            'anio' => $year,
            'cantidad' => $count,
        ]);
    }

    // ✅ LLENA MESES ANTERIORES
    public function backfill()
    {
        $authUser = Auth::guard('web')->user() ?? Auth::guard('sub')->user();
        if (!$authUser) return response()->json(['message' => 'No autenticado.'], 401);

        $agencyCode = $authUser->agency;
        if (!$agencyCode) return response()->json(['message' => 'El usuario no tiene agency vinculada.'], 403);

        $ownerUser = User::where('agency', $agencyCode)->first();
        if (!$ownerUser || !$ownerUser->twilio_number) {
            return response()->json(['message' => 'No se encontró número Twilio asignado para esta agency.'], 403);
        }

        $twilioNumber = $ownerUser->twilio_number;

        // Detecta meses/años existentes SOLO outbound-api
        $months = DB::table('sms')
            ->selectRaw('YEAR(COALESCE(date_sent, date_created)) as anio, MONTH(COALESCE(date_sent, date_created)) as mes')
            ->where('from', $twilioNumber)
            ->where('direction', 'outbound-api')
            ->groupByRaw('YEAR(COALESCE(date_sent, date_created)), MONTH(COALESCE(date_sent, date_created))')
            ->orderByRaw('anio ASC, mes ASC')
            ->get();

        $processed = 0;

        foreach ($months as $m) {
            $year  = (int) $m->anio;
            $month = (int) $m->mes;

            $start = Carbon::create($year, $month, 1)->startOfMonth();
            $end   = Carbon::create($year, $month, 1)->endOfMonth();

            $count = DB::table('sms')
                ->where('from', $twilioNumber)
                ->where('direction', 'outbound-api')
                ->whereBetween(DB::raw('COALESCE(date_sent, date_created)'), [$start, $end])
                ->count();

            $this->upsertMonthly($agencyCode, $twilioNumber, $month, $year, $count);
            $processed++;
        }

        return response()->json([
            'message' => 'Backfill completado.',
            'agency_code' => $agencyCode,
            'twilio_number' => $twilioNumber,
            'meses_procesados' => $processed,
        ]);
    }

    private function upsertMonthly(string $agencyCode, string $twilioNumber, int $month, int $year, int $count): void
    {
        $docDb = DB::connection('doc_config');

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
    }
}
