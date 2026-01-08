<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class AccountController extends Controller
{
    public function show()
    {
        // 1) Obtener usuario autenticado (user o sub_user)
        $authUser = Auth::guard('web')->user() ?? Auth::guard('sub')->user();

        if (!$authUser) {
            abort(403, 'Usuario no autenticado');
        }

        $agencyCode = $authUser->agency;

        if (!$agencyCode) {
            abort(403, 'El usuario no tiene agencia vinculada.');
        }

        // 2) Obtener registro de agency
        $agency = DB::table('agency')
            ->where('agency_code', $agencyCode)
            ->first();

        if (!$agency) {
            abort(403, 'No se encontró la agency para este usuario.');
        }

        // 3) Obtener el usuario dueño de la agency (tabla users) para sacar el twilio_number
        $ownerUser = User::where('agency', $agencyCode)->first();

        if (!$ownerUser || !$ownerUser->twilio_number) {
            abort(403, 'No se encontró número Twilio asignado para esta agency.');
        }

        $twilioNumber = $ownerUser->twilio_number;

        // 4) Obtener plan desde BD doc_config.limits
        $plan = DB::connection('doc_config')
            ->table('limits')
            ->where('account_type', $agency->account_type)
            ->first();

        if (!$plan) {
            // fallback a un plan por defecto
            $plan = DB::connection('doc_config')
                ->table('limits')
                ->where('account_type', 'P1')
                ->first();
        }

        $smsLimit  = (int) $plan->msg_limit;
        $docLimit  = (int) $plan->doc_limit;
        $userLimit = (int) $plan->user_limit;

        // 5) Fechas
        $today      = Carbon::today();
        $startMonth = Carbon::now()->startOfMonth();
        $endMonth   = Carbon::now()->endOfMonth();

        // ============================
        //      CONTADOR DE SMS
        // ============================

        // Diario (hoy)
        $dailySmsCount = DB::table('sms')
            ->where('from', $twilioNumber)
            ->where('direction', 'outbound-api')
            ->whereDate('date_created', $today)
            ->count();

        // Mensual
        $monthlySmsCount = DB::table('sms')
            ->where('from', $twilioNumber)
            ->where('direction', 'outbound-api')
            ->whereBetween('date_sent', [$startMonth, $endMonth])
            ->count();

        $isSmsOverLimit = $monthlySmsCount >= $smsLimit;

        // ============================
        //      CONTADOR DOCS
        // ============================

        // Aún no tienes tabla de documentos, lo dejamos en 0 por ahora
        $monthlyDocCount = 0;
        $isDocsOverLimit = false;

        // ============================
        //      CONTADOR USERS
        // ============================

        $totalUsers =
            DB::table('users')->where('agency', $agencyCode)->count() +
            DB::table('sub_users')->where('agency', $agencyCode)->count();

        $isUserOverLimit = $totalUsers >= $userLimit;

        // 6) Actualizar contadores en agency (opcional)
        DB::table('agency')
            ->where('agency_code', $agencyCode)
            ->update([
                'message_counter' => $dailySmsCount,
                'doc_counter'     => $monthlyDocCount,
            ]);

        // 7) Enviar datos a la vista
        return view('account', [
            'agency'           => $agency,

            // SMS
            'dailySmsCount'    => $dailySmsCount,
            'monthlySmsCount'  => $monthlySmsCount,
            'smsLimit'         => $smsLimit,
            'isSmsOverLimit'   => $isSmsOverLimit,

            // DOCS
            'monthlyDocCount'  => $monthlyDocCount,
            'docLimit'         => $docLimit,
            'isDocsOverLimit'  => $isDocsOverLimit,

            // USERS
            'totalUsers'       => $totalUsers,
            'userLimit'        => $userLimit,
            'isUserOverLimit'  => $isUserOverLimit,

            // Info extra
            'plan'             => $plan,
            'twilioNumber'     => $twilioNumber,
        ]);
    }
}
