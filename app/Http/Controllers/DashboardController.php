<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use App\Models\Customer;
use App\Models\Reminder;
use Illuminate\Support\Facades\Schema;

class DashboardController extends Controller
{
    public function show()
    {
        // Obtener usuario autenticado (funciona también con remember me)
        $user = Auth::guard('web')->user() ?? Auth::guard('sub')->user();

        // En caso de no estar autenticado, redirige al login
        if (!$user) {
            return redirect()->route('login');
        }

        $agency = $user->agency;

        // 🔹 Obtener los últimos 50 customers
        $customers = Customer::where('agency', $agency)
            ->orderBy('ID', 'desc')
            ->take(50)
            ->get();

        // ids de customers
        $customerIds = $customers->pluck('ID')->filter()->values()->all();

        // Consulta independiente
        $policyCounts = $this->getPolicyCountsByCustomerId($customerIds);

        // 🔹 OBTENER REMINDERS SEGÚN SESIÓN
        $webUser = Auth::guard('web')->user();
        $subUser = Auth::guard('sub')->user();

        $reminders = collect();

        if ($webUser) {
            $reminders = Reminder::where('remind_to_type', 'user')
                ->where('remind_to_id', $webUser->id)
                ->orderBy('remind_at', 'asc')
                ->get();
        }

        if ($subUser) {
            $reminders = Reminder::where('remind_to_type', 'sub')
                ->where('remind_to_id', $subUser->id)
                ->orderBy('remind_at', 'asc')
                ->get();
        }

        $remindersCount = $reminders->count();

        /*
        |--------------------------------------------------------------------------
        | QUICK ACCESS COUNTS
        |--------------------------------------------------------------------------
        */

        $totalCustomers = DB::table('customers')
            ->where('agency', $agency)
            ->count();

        $commercialCount = DB::table('company')
            ->whereRaw('LOWER(type) = ?', ['commercial'])
            ->count();

        $personalCount = DB::table('company')
            ->whereRaw('LOWER(type) = ?', ['personal'])
            ->count();

        $tasksCount = DB::table('tasks')
            ->where('agency', $agency)
            ->count();

        // Obtener twilio_number desde doc_config.sms_monthly_counters usando agency_code
        $twilioNumber = DB::connection('doc_config')
            ->table('sms_monthly_counters')
            ->where('agency_code', $agency)
            ->whereNotNull('twilio_number')
            ->orderByDesc('anio')
            ->orderByDesc('mes')
            ->value('twilio_number');

        $todayMessagesCount = 0;

        if (!empty($twilioNumber)) {
            $todayMessagesCount = DB::table('sms')
                ->where('from', $twilioNumber)
                ->where('direction', 'outbound-api')
                ->whereDate('date_created', Carbon::today()->toDateString())
                ->count();
        }

        $documentsCount = DB::table('documents')
            ->count();

        $recentDocuments = DB::table('documents as d')
            ->leftJoin('pdf_overlays as p', 'p.id', '=', 'd.template_id')
            ->select(
                'd.id',
                'd.insured_name',
                'd.date',
                'd.time',
                'p.template_name'
            )
            ->orderByDesc('d.id')
            ->limit(6)
            ->get();

        /*
        |--------------------------------------------------------------------------
        | WEEKLY INCOME REAL PARA LA GRÁFICA
        |--------------------------------------------------------------------------
        */
        $weekStart = now()->startOfWeek(Carbon::MONDAY);
        $weekEnd   = now()->endOfWeek(Carbon::SUNDAY);

        $weeklyBase = [
            'Monday'    => 0,
            'Tuesday'   => 0,
            'Wednesday' => 0,
            'Thursday'  => 0,
            'Friday'    => 0,
            'Saturday'  => 0,
            'Sunday'    => 0,
        ];

        $weeklyInvoices = DB::table('invoices')
            ->where('agency', $agency)
            ->whereNotNull('creation_date')
            ->whereDate('creation_date', '>=', $weekStart->toDateString())
            ->whereDate('creation_date', '<=', $weekEnd->toDateString())
            ->select('creation_date', 'inv_prices')
            ->get();

        foreach ($weeklyInvoices as $invoice) {
            try {
                $dayName = Carbon::parse($invoice->creation_date)->englishDayOfWeek;
            } catch (\Exception $e) {
                continue;
            }

            if (!array_key_exists($dayName, $weeklyBase)) {
                continue;
            }

            $prices = json_decode($invoice->inv_prices ?? '{}', true);
            $amount = (float) ($prices['grand_total'] ?? 0);

            $weeklyBase[$dayName] += $amount;
        }

        $maxAmount = !empty($weeklyBase) ? max($weeklyBase) : 0;

        $weeklyIncome = collect($weeklyBase)->map(function ($amount, $day) use ($maxAmount) {
            $percentage = $maxAmount > 0 ? round(($amount / $maxAmount) * 100, 2) : 0;

            return [
                'day' => $day,
                'amount' => $amount,
                'percentage' => $percentage,
            ];
        })->values();

        return view('dashboard', [
            'username'           => $user->name ?? $user->username,
            'customers'          => $customers,
            'reminders'          => $reminders,
            'remindersCount'     => $remindersCount,
            'policyCounts'       => $policyCounts,
            'recentDocuments'    => $recentDocuments,
            'weeklyIncome'       => $weeklyIncome,

            // Quick access
            'totalCustomers'     => $totalCustomers,
            'commercialCount'    => $commercialCount,
            'personalCount'      => $personalCount,
            'tasksCount'         => $tasksCount,
            'todayMessagesCount' => $todayMessagesCount,
            'documentsCount'     => $documentsCount,
        ]);
    }

    private function getPolicyCountsByCustomerId(array $customerIds): array
    {
        if (empty($customerIds)) return [];

        return DB::table('policies')
            ->whereIn('customer_id', $customerIds)
            ->selectRaw('customer_id, COUNT(*) as total')
            ->groupBy('customer_id')
            ->pluck('total', 'customer_id')
            ->toArray();
    }

    public function exportSelectedCustomersCsv(Request $request)
    {
        $authUser = Auth::guard('web')->user() ?? Auth::guard('sub')->user();

        if (!$authUser) {
            return response()->json([
                'ok' => false,
                'error' => 'not_auth',
            ], 401);
        }

        $agency = $authUser->agency;

        $ids = $request->input('ids', []);

        if (!is_array($ids)) {
            $ids = [];
        }

        $ids = collect($ids)
            ->map(fn($id) => (int) $id)
            ->filter()
            ->unique()
            ->values()
            ->all();

        if (empty($ids)) {
            return response()->json([
                'ok' => false,
                'error' => 'no_customers_selected',
            ], 422);
        }

        $columns = Schema::getColumnListing('customers');

        /*
    |--------------------------------------------------------------------------
    | Excluir columnas que no son útiles en CSV
    |--------------------------------------------------------------------------
    | En Reports normalmente se ocultan Picture y Alert.
    */
        $excludedColumns = ['Picture', 'Alert'];

        $exportColumns = array_values(array_filter($columns, function ($column) use ($excludedColumns) {
            return !in_array($column, $excludedColumns);
        }));

        $customers = DB::table('customers')
            ->where('agency', $agency)
            ->whereIn('ID', $ids)
            ->orderBy('ID', 'asc')
            ->get($exportColumns);

        if ($customers->isEmpty()) {
            return response()->json([
                'ok' => false,
                'error' => 'customers_not_found',
            ], 404);
        }

        $fileName = 'customers_selected_' . now()->format('Ymd_His') . '.csv';

        return response()->streamDownload(function () use ($customers, $exportColumns) {
            $handle = fopen('php://output', 'w');

            // BOM para que Excel abra bien acentos y caracteres especiales
            fwrite($handle, "\xEF\xBB\xBF");

            // Header
            fputcsv($handle, $exportColumns);

            // Rows
            foreach ($customers as $customer) {
                $row = [];

                foreach ($exportColumns as $column) {
                    $value = $customer->{$column} ?? '';

                    if (is_array($value) || is_object($value)) {
                        $value = json_encode($value, JSON_UNESCAPED_UNICODE);
                    }

                    $row[] = $value;
                }

                fputcsv($handle, $row);
            }

            fclose($handle);
        }, $fileName, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function deleteSelectedCustomers(Request $request)
    {
        $authUser = Auth::guard('web')->user() ?? Auth::guard('sub')->user();

        if (!$authUser) {
            return response()->json([
                'ok' => false,
                'error' => 'not_auth',
            ], 401);
        }

        $agency = $authUser->agency;

        $ids = $request->input('ids', []);

        if (!is_array($ids)) {
            $ids = [];
        }

        $ids = collect($ids)
            ->map(fn($id) => (int) $id)
            ->filter()
            ->unique()
            ->values()
            ->all();

        if (empty($ids)) {
            return response()->json([
                'ok' => false,
                'error' => 'no_customers_selected',
            ], 422);
        }

        $deleted = DB::table('customers')
            ->where('agency', $agency)
            ->whereIn('ID', $ids)
            ->delete();

        return response()->json([
            'ok' => true,
            'deleted' => $deleted,
        ]);
    }
}
