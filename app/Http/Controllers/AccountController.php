<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use App\Models\Limit;
use App\Models\Agency;
use App\Models\User;

class AccountController extends Controller
{
    public function show()
    {
        $authUser = auth()->user();
        $agencyCode = $authUser->agency;

        // Agency
        $agency = Agency::where('agency_code', $agencyCode)->firstOrFail();

        // Obtener numero Twilio del usuario dueño de la agency
        $ownerUser = User::where('agency', $agencyCode)->first();
        $twilioNumber = $ownerUser->twilio_number;

        // Obtener plan en doc_config.limits
        $plan = Limit::on('doc_config')
            ->where('account_type', $agency->account_type)
            ->first();

        if (!$plan) {
            $plan = Limit::on('doc_config')->where('account_type', 'P1')->first();
        }

        // Fechas
        $today = Carbon::today();
        $startMonth = Carbon::now()->startOfMonth();
        $endMonth   = Carbon::now()->endOfMonth();

        // ============================
        //      CONTADOR SMS
        // ============================

        // SMS enviados hoy
        $dailySmsCount = DB::table('sms')
            ->where('from', $twilioNumber)
            ->where('direction', 'outbound-api')
            ->whereDate('created_at', $today)
            ->count();

        // SMS enviados en el mes
        $monthlySmsCount = DB::table('sms')
            ->where('from', $twilioNumber)
            ->where('direction', 'outbound-api')
            ->whereBetween('created_at', [$startMonth, $endMonth])
            ->count();

        $smsLimit = (int) $plan->msg_limit;
        $isSmsOverLimit = $monthlySmsCount >= $smsLimit;

        // ============================
        //      CONTADOR DOCS
        // ============================

        // Aún no tienes tabla de documentos
        $monthlyDocCount = 0;
        $docLimit = (int) $plan->doc_limit;
        $isDocsOverLimit = false;

        // ============================
        //      CONTADOR USERS
        // ============================

        $totalUsers =
            DB::table('users')->where('agency', $agencyCode)->count() +
            DB::table('sub_users')->where('agency', $agencyCode)->count();

        $userLimit = (int) $plan->user_limit;
        $isUserOverLimit = $totalUsers >= $userLimit;

        // Guardar counters
        $agency->message_counter = $dailySmsCount;
        $agency->doc_counter = $monthlyDocCount;
        $agency->save();

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

            'plan'             => $plan,
            'twilioNumber'     => $twilioNumber,
        ]);
    }
}
