<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ReportsController extends Controller
{
    public function index()
    {
        $authUser = Auth::guard('web')->user() ?? Auth::guard('sub')->user();
        if (!$authUser) {
            return redirect()->route('login');
        }

        $agency = $authUser->agency ?? null;
        $agentOptions = $this->getAgencyAgentOptions($agency);

        return view('reports', compact('agentOptions'));
    }

    public function invoicesData(Request $request)
    {
        $authUser = Auth::guard('web')->user() ?? Auth::guard('sub')->user();
        if (!$authUser) {
            return response()->json(['rows' => []], 401);
        }

        $agency = $authUser->agency ?? null;
        $agentFilter = trim((string) $request->get('agent', ''));

        $dateColumn = $this->resolveFirstExistingColumn('invoices', [
            'payment_date',
            'date',
            'creation_date',
            'created_at',
        ]) ?? 'created_at';

        /*
        |--------------------------------------------------------------------------
        | 1) Mapa global de PAYMENT #
        |    Se calcula sobre TODOS los invoices del agency.
        |    Así, si borras uno, la numeración se recorre sola.
        |--------------------------------------------------------------------------
        */
        $sequenceQuery = DB::table('invoices as i')->select('i.id');
        $this->applyAgencyFilter($sequenceQuery, 'i', $agency);

        /*
|--------------------------------------------------------------------------
| PAYMENT # consecutivo:
| El más antiguo recibe 1, y el más nuevo el número más alto.
|--------------------------------------------------------------------------
*/
        if (Schema::hasColumn('invoices', 'created_at')) {
            $sequenceQuery->orderBy('i.created_at', 'asc');
        } elseif (Schema::hasColumn('invoices', 'creation_date')) {
            $sequenceQuery->orderBy('i.creation_date', 'asc');
        } elseif (Schema::hasColumn('invoices', 'payment_date')) {
            $sequenceQuery->orderBy('i.payment_date', 'asc');
        }

        $sequenceMap = [];
        foreach ($sequenceQuery->get() as $index => $row) {
            $sequenceMap[$row->id] = $index + 1;
        }

        /*
        |--------------------------------------------------------------------------
        | 2) Query del reporte filtrado
        |--------------------------------------------------------------------------
        */
        $query = DB::table('invoices as i');

        if (Schema::hasColumn('invoices', 'customer_id')) {
            $query->leftJoin('customers as c', 'c.ID', '=', 'i.customer_id');

            if (Schema::hasColumn('invoices', 'insured_name')) {
                $query->select('i.*', DB::raw('COALESCE(c.Name, i.insured_name) as customer_name'));
            } else {
                $query->select('i.*', DB::raw('c.Name as customer_name'));
            }
        } else {
            $query->select('i.*');
        }

        $this->applyAgencyFilter($query, 'i', $agency);
        $this->applyAgentFilter($query, 'i', $agentFilter);
        $this->applyPeriodFilter(
            $query,
            'i',
            $dateColumn,
            $request->get('period', 'all'),
            $request->get('from'),
            $request->get('to')
        );

        /*
|--------------------------------------------------------------------------
| Mostrar primero los más nuevos:
| así el Payment # más alto aparece arriba.
|--------------------------------------------------------------------------
*/
        if (Schema::hasColumn('invoices', 'created_at')) {
            $query->orderBy('i.created_at', 'desc');
        } elseif (Schema::hasColumn('invoices', 'creation_date')) {
            $query->orderBy('i.creation_date', 'desc');
        } elseif (Schema::hasColumn('invoices', 'payment_date')) {
            $query->orderBy('i.payment_date', 'desc');
        }

        $invoices = $query->get();

        $rows = [];

        foreach ($invoices as $invoice) {
            $paymentNumber = $sequenceMap[$invoice->id] ?? 0;

            $rows = array_merge(
                $rows,
                $this->transformInvoiceToReportRows($invoice, $paymentNumber, $dateColumn)
            );
        }

        return response()->json([
            'rows' => $rows,
        ]);
    }

    private function transformInvoiceToReportRows(object $invoice, int $paymentNumber, string $dateColumn): array
    {
        $row = (array) $invoice;

        $dateValue = $row[$dateColumn] ?? ($row['payment_date'] ?? '');

        $invoiceNumber = $row['invoice_number'] ?? '';
        $customer = $row['customer_name'] ?? '';
        $paymentMode = $row['payment_method'] ?? '';

        $feeTotal = $this->sanitizeNumber($row['fee'] ?? 0);
        $premiumTotal = $this->sanitizeNumber($row['premium'] ?? 0);

        $policyNumber = $row['policy_number'] ?? '';
        $saleAgent = $row['created_by_name'] ?? '';

        $feeSplitEnabled = $this->isTruthy($row['fee_split'] ?? false);
        $premiumSplitEnabled = $this->isTruthy($row['premium_split'] ?? false);

        $description = $this->extractDescription($row);
        $amount = $this->extractGrandTotal($row);


        /*
        |--------------------------------------------------------------------------
        | Caso 1: sin split en fee ni premium = 1 sola fila
        |--------------------------------------------------------------------------
        */
        if (!$feeSplitEnabled && !$premiumSplitEnabled) {
            return [[
                'payment_number' => $paymentNumber,
                'date'           => $this->formatDate($dateValue),
                'invoice_number' => $invoiceNumber,
                'customer'       => $customer,
                'payment_mode'   => $paymentMode,
                'fee'            => $feeTotal,
                'premium'        => $premiumTotal,
                'policy_number'  => $policyNumber,
                'description'    => $description,
                'amount'         => $amount,
                'sale_agent'     => $saleAgent,
            ]];
        }

        /*
        |--------------------------------------------------------------------------
        | Caso 2: con split
        | Orden:
        |   - premium rows primero
        |   - fee rows después
        |--------------------------------------------------------------------------
        */
        $segments = [];

        if ($premiumSplitEnabled) {
            $premiumRows = $this->extractSplitRows($row, 'premium', $paymentMode, 'premium');

            if (empty($premiumRows) && $premiumTotal > 0) {
                $premiumRows[] = [
                    'payment_mode' => $paymentMode,
                    'fee' => 0,
                    'premium' => $premiumTotal,
                ];
            }

            $segments = array_merge($segments, $premiumRows);
        } elseif ($premiumTotal > 0) {
            $segments[] = [
                'payment_mode' => $paymentMode,
                'fee' => 0,
                'premium' => $premiumTotal,
            ];
        }

        if ($feeSplitEnabled) {
            $feeRows = $this->extractSplitRows($row, 'fee', $paymentMode, 'fee');

            if (empty($feeRows) && $feeTotal > 0) {
                $feeRows[] = [
                    'payment_mode' => $paymentMode,
                    'fee' => $feeTotal,
                    'premium' => 0,
                ];
            }

            $segments = array_merge($segments, $feeRows);
        } elseif ($feeTotal > 0) {
            $segments[] = [
                'payment_mode' => $paymentMode,
                'fee' => $feeTotal,
                'premium' => 0,
            ];
        }

        if (empty($segments)) {
            $segments[] = [
                'payment_mode' => $paymentMode,
                'fee' => $feeTotal,
                'premium' => $premiumTotal,
            ];
        }

        $reportRows = [];

        foreach ($segments as $index => $segment) {
            $reportRows[] = [
                'payment_number' => $paymentNumber,
                'date'           => $this->formatDate($dateValue),
                'invoice_number' => $invoiceNumber,
                'customer'       => $customer,
                'payment_mode'   => $segment['payment_mode'] ?? $paymentMode,
                'fee'            => $segment['fee'] ?? 0,
                'premium'        => $segment['premium'] ?? 0,
                'policy_number'  => $policyNumber,
                'description'    => $description,
                'amount'         => $index === 0 ? $amount : 0,
                'sale_agent'     => $saleAgent,
            ];
        }

        return $reportRows;
    }

    private function extractSplitRows(array $row, string $prefix, string $fallbackMode, string $type): array
    {
        $rows = [];

        if ($prefix === 'fee') {
            $method1 = $row['fee_payment1_method'] ?? $fallbackMode;
            $amount1 = $this->sanitizeNumber($row['fee_payment1_value'] ?? 0);

            $method2 = $row['fee_payment2_method'] ?? $fallbackMode;
            $amount2 = $this->sanitizeNumber($row['fee_payment2_value'] ?? 0);
        } else {
            $method1 = $row['premium_payment1_method'] ?? $fallbackMode;
            $amount1 = $this->sanitizeNumber($row['premium_payment1_value'] ?? 0);

            $method2 = $row['premium_payment2_method'] ?? $fallbackMode;
            $amount2 = $this->sanitizeNumber($row['premium_payment2_value'] ?? 0);
        }

        if ($amount1 > 0 || !empty($method1)) {
            $rows[] = [
                'payment_mode' => $method1 ?: $fallbackMode,
                'fee' => $type === 'fee' ? $amount1 : 0,
                'premium' => $type === 'premium' ? $amount1 : 0,
            ];
        }

        if ($amount2 > 0 || !empty($method2)) {
            $rows[] = [
                'payment_mode' => $method2 ?: $fallbackMode,
                'fee' => $type === 'fee' ? $amount2 : 0,
                'premium' => $type === 'premium' ? $amount2 : 0,
            ];
        }

        return $rows;
    }

    private function extractDescription(array $row): string
    {
        $direct = $this->pick($row, [
            'description',
            'item',
            'service',
            'concept',
        ]);

        if (!empty($direct)) {
            return (string) $direct;
        }

        if (!empty($row['inv_prices'])) {
            $decoded = json_decode($row['inv_prices'], true);

            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $items = [];

                foreach (($decoded['rows'] ?? []) as $detail) {
                    $label = $detail['item']
                        ?? $detail['description']
                        ?? $detail['name']
                        ?? null;

                    if ($label) {
                        $items[] = trim((string) $label);
                    }
                }

                $items = array_values(array_unique(array_filter($items)));

                if (!empty($items)) {
                    return implode(' | ', $items);
                }
            }
        }

        return '';
    }

    private function extractGrandTotal(array $row): float
    {
        if (!empty($row['inv_prices'])) {
            $decoded = json_decode($row['inv_prices'], true);

            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                if (isset($decoded['grand_total'])) {
                    return $this->sanitizeNumber($decoded['grand_total']);
                }

                $itemsTotal = 0;

                foreach (($decoded['rows'] ?? []) as $detail) {
                    if (isset($detail['row_total'])) {
                        $itemsTotal += $this->sanitizeNumber($detail['row_total']);
                    } else {
                        $qty = $this->sanitizeNumber($detail['amount'] ?? $detail['qty'] ?? 0);
                        $price = $this->sanitizeNumber($detail['price'] ?? 0);
                        $itemsTotal += ($qty * $price);
                    }
                }

                return $itemsTotal;
            }
        }

        return 0;
    }

    private function applyAgencyFilter($query, string $alias, ?string $agency): void
    {
        if (!$agency) {
            return;
        }

        if (Schema::hasColumn('invoices', 'agency')) {
            $query->where("$alias.agency", $agency);
        } elseif (Schema::hasColumn('invoices', 'agency_code')) {
            $query->where("$alias.agency_code", $agency);
        }
    }

    private function applyPeriodFilter($query, string $alias, ?string $dateColumn, string $period, ?string $from, ?string $to): void
    {
        if (!$dateColumn || !Schema::hasColumn('invoices', $dateColumn)) {
            return;
        }

        $now = Carbon::now();

        switch ($period) {
            case 'this_month':
                $query->whereBetween("$alias.$dateColumn", [
                    $now->copy()->startOfMonth(),
                    $now->copy()->endOfMonth(),
                ]);
                break;

            case 'last_month':
                $query->whereBetween("$alias.$dateColumn", [
                    $now->copy()->subMonthNoOverflow()->startOfMonth(),
                    $now->copy()->subMonthNoOverflow()->endOfMonth(),
                ]);
                break;

            case 'this_year':
                $query->whereBetween("$alias.$dateColumn", [
                    $now->copy()->startOfYear(),
                    $now->copy()->endOfYear(),
                ]);
                break;

            case 'last_year':
                $query->whereBetween("$alias.$dateColumn", [
                    $now->copy()->subYear()->startOfYear(),
                    $now->copy()->subYear()->endOfYear(),
                ]);
                break;

            case 'last_3_months':
                $query->whereBetween("$alias.$dateColumn", [
                    $now->copy()->subMonthsNoOverflow(2)->startOfMonth(),
                    $now->copy()->endOfMonth(),
                ]);
                break;

            case 'last_6_months':
                $query->whereBetween("$alias.$dateColumn", [
                    $now->copy()->subMonthsNoOverflow(5)->startOfMonth(),
                    $now->copy()->endOfMonth(),
                ]);
                break;

            case 'last_12_months':
                $query->whereBetween("$alias.$dateColumn", [
                    $now->copy()->subMonthsNoOverflow(11)->startOfMonth(),
                    $now->copy()->endOfMonth(),
                ]);
                break;

            case 'custom':
                if ($from && $to) {
                    $fromDate = Carbon::parse($from)->startOfDay();
                    $toDate = Carbon::parse($to)->endOfDay();

                    if ($fromDate->gt($toDate)) {
                        [$fromDate, $toDate] = [$toDate, $fromDate];
                    }

                    $query->whereBetween("$alias.$dateColumn", [$fromDate, $toDate]);
                }
                break;

            case 'all':
            default:
                break;
        }
    }

    private function resolveFirstExistingColumn(string $table, array $columns): ?string
    {
        foreach ($columns as $column) {
            if (Schema::hasColumn($table, $column)) {
                return $column;
            }
        }

        return null;
    }

    private function pick(array $row, array $keys, $default = null)
    {
        foreach ($keys as $key) {
            if (array_key_exists($key, $row) && $row[$key] !== null && $row[$key] !== '') {
                return $row[$key];
            }
        }

        return $default;
    }

    private function sanitizeNumber($value): float
    {
        if ($value === null || $value === '') {
            return 0;
        }

        if (is_numeric($value)) {
            return (float) $value;
        }

        return (float) str_replace([',', '$', ' '], '', (string) $value);
    }

    private function isTruthy($value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        return in_array(strtolower((string) $value), ['1', 'true', 'yes', 'on'], true);
    }

    private function formatDate($value): string
    {
        if (!$value) {
            return '';
        }

        try {
            return Carbon::parse($value)->format('Y-m-d');
        } catch (\Throwable $e) {
            return (string) $value;
        }
    }


    private function getAgencyAgentOptions(?string $agency): array
    {
        if (!$agency) {
            return [];
        }

        $names = [];

        /*
    |--------------------------------------------------------------------------
    | Users
    |--------------------------------------------------------------------------
    */
        if (Schema::hasTable('users') && Schema::hasColumn('users', 'agency')) {
            $userNameColumn = null;

            if (Schema::hasColumn('users', 'name')) {
                $userNameColumn = 'name';
            } elseif (Schema::hasColumn('users', 'username')) {
                $userNameColumn = 'username';
            }

            if ($userNameColumn) {
                $userNames = DB::table('users')
                    ->where('agency', $agency)
                    ->whereNotNull($userNameColumn)
                    ->pluck($userNameColumn)
                    ->toArray();

                $names = array_merge($names, $userNames);
            }
        }

        /*
    |--------------------------------------------------------------------------
    | Sub Users
    |--------------------------------------------------------------------------
    */
        if (Schema::hasTable('sub_users') && Schema::hasColumn('sub_users', 'agency')) {
            $subUserNameColumn = null;

            if (Schema::hasColumn('sub_users', 'name')) {
                $subUserNameColumn = 'name';
            } elseif (Schema::hasColumn('sub_users', 'username')) {
                $subUserNameColumn = 'username';
            }

            if ($subUserNameColumn) {
                $subUserNames = DB::table('sub_users')
                    ->where('agency', $agency)
                    ->whereNotNull($subUserNameColumn)
                    ->pluck($subUserNameColumn)
                    ->toArray();

                $names = array_merge($names, $subUserNames);
            }
        }

        /*
    |--------------------------------------------------------------------------
    | Fallback: por si no encuentra nombres en users/sub_users,
    | toma lo que exista en invoices.created_by_name
    |--------------------------------------------------------------------------
    */
        if (empty($names) && Schema::hasColumn('invoices', 'created_by_name')) {
            $invoiceNames = DB::table('invoices')
                ->where('agency', $agency)
                ->whereNotNull('created_by_name')
                ->distinct()
                ->pluck('created_by_name')
                ->toArray();

            $names = array_merge($names, $invoiceNames);
        }

        $names = array_map(fn($value) => trim((string) $value), $names);
        $names = array_filter($names, fn($value) => $value !== '');
        $names = array_values(array_unique($names));

        natcasesort($names);

        return array_values($names);
    }

    private function applyAgentFilter($query, string $alias, string $agentFilter): void
    {
        if ($agentFilter === '') {
            return;
        }

        if (Schema::hasColumn('invoices', 'created_by_name')) {
            $query->where("$alias.created_by_name", 'like', '%' . $agentFilter . '%');
        }
    }



    // ESTIMATES

    public function estimatesData(Request $request)
    {
        $authUser = Auth::guard('web')->user() ?? Auth::guard('sub')->user();
        if (!$authUser) {
            return response()->json(['rows' => []], 401);
        }

        $agency = $authUser->agency ?? null;
        $agentFilter = trim((string) $request->get('agent', ''));

        $dateColumn = $this->resolveFirstExistingColumn('estimates', [
            'payment_date',
            'date',
            'creation_date',
            'created_at',
        ]) ?? 'created_at';

        /*
    |--------------------------------------------------------------------------
    | Consecutivo global para ESTIMATES
    |--------------------------------------------------------------------------
    */
        $sequenceQuery = DB::table('estimates as e')->select('e.id');

        if ($agency && Schema::hasColumn('estimates', 'agency')) {
            $sequenceQuery->where('e.agency', $agency);
        }

        if (Schema::hasColumn('estimates', 'created_at')) {
            $sequenceQuery->orderBy('e.created_at', 'asc');
        } elseif (Schema::hasColumn('estimates', 'creation_date')) {
            $sequenceQuery->orderBy('e.creation_date', 'asc');
        } elseif (Schema::hasColumn('estimates', 'payment_date')) {
            $sequenceQuery->orderBy('e.payment_date', 'asc');
        } else {
            $sequenceQuery->orderBy('e.id', 'asc');
        }

        $sequenceMap = [];
        foreach ($sequenceQuery->get() as $index => $row) {
            $sequenceMap[$row->id] = $index + 1;
        }

        /*
    |--------------------------------------------------------------------------
    | Query principal
    |--------------------------------------------------------------------------
    */
        $query = DB::table('estimates as e');

        if (Schema::hasColumn('estimates', 'customer_id')) {
            $query->leftJoin('customers as c', 'c.ID', '=', 'e.customer_id')
                ->select('e.*', DB::raw('COALESCE(c.Name, "") as customer_name'));
        } else {
            $query->select('e.*');
        }

        if ($agency && Schema::hasColumn('estimates', 'agency')) {
            $query->where('e.agency', $agency);
        }

        if ($agentFilter !== '' && Schema::hasColumn('estimates', 'created_by_name')) {
            $query->where('e.created_by_name', 'like', '%' . $agentFilter . '%');
        }

        if ($dateColumn && Schema::hasColumn('estimates', $dateColumn)) {
            $period = $request->get('period', 'all');
            $from = $request->get('from');
            $to = $request->get('to');
            $now = \Carbon\Carbon::now();

            switch ($period) {
                case 'this_month':
                    $query->whereBetween("e.$dateColumn", [
                        $now->copy()->startOfMonth(),
                        $now->copy()->endOfMonth(),
                    ]);
                    break;

                case 'last_month':
                    $query->whereBetween("e.$dateColumn", [
                        $now->copy()->subMonthNoOverflow()->startOfMonth(),
                        $now->copy()->subMonthNoOverflow()->endOfMonth(),
                    ]);
                    break;

                case 'this_year':
                    $query->whereBetween("e.$dateColumn", [
                        $now->copy()->startOfYear(),
                        $now->copy()->endOfYear(),
                    ]);
                    break;

                case 'last_year':
                    $query->whereBetween("e.$dateColumn", [
                        $now->copy()->subYear()->startOfYear(),
                        $now->copy()->subYear()->endOfYear(),
                    ]);
                    break;

                case 'last_3_months':
                    $query->whereBetween("e.$dateColumn", [
                        $now->copy()->subMonthsNoOverflow(2)->startOfMonth(),
                        $now->copy()->endOfMonth(),
                    ]);
                    break;

                case 'last_6_months':
                    $query->whereBetween("e.$dateColumn", [
                        $now->copy()->subMonthsNoOverflow(5)->startOfMonth(),
                        $now->copy()->endOfMonth(),
                    ]);
                    break;

                case 'last_12_months':
                    $query->whereBetween("e.$dateColumn", [
                        $now->copy()->subMonthsNoOverflow(11)->startOfMonth(),
                        $now->copy()->endOfMonth(),
                    ]);
                    break;

                case 'custom':
                    if ($from && $to) {
                        $fromDate = \Carbon\Carbon::parse($from)->startOfDay();
                        $toDate = \Carbon\Carbon::parse($to)->endOfDay();

                        if ($fromDate->gt($toDate)) {
                            [$fromDate, $toDate] = [$toDate, $fromDate];
                        }

                        $query->whereBetween("e.$dateColumn", [$fromDate, $toDate]);
                    }
                    break;
            }
        }

        /*
    |--------------------------------------------------------------------------
    | Mostrar primero los más nuevos
    |--------------------------------------------------------------------------
    */
        if (Schema::hasColumn('estimates', 'created_at')) {
            $query->orderBy('e.created_at', 'desc');
        } elseif (Schema::hasColumn('estimates', 'creation_date')) {
            $query->orderBy('e.creation_date', 'desc');
        } elseif (Schema::hasColumn('estimates', 'payment_date')) {
            $query->orderBy('e.payment_date', 'desc');
        } else {
            $query->orderBy('e.id', 'desc');
        }

        $estimates = $query->get();
        $rows = [];

        foreach ($estimates as $estimate) {
            $rows = array_merge(
                $rows,
                $this->transformEstimateToReportRows(
                    $estimate,
                    $sequenceMap[$estimate->id] ?? 0,
                    $dateColumn
                )
            );
        }

        return response()->json([
            'rows' => $rows,
        ]);
    }

    private function transformEstimateToReportRows(object $estimate, int $paymentNumber, string $dateColumn): array
    {
        $row = (array) $estimate;

        $dateValue = $row[$dateColumn] ?? ($row['payment_date'] ?? '');

        $estimateNumber = $row['estimate_number'] ?? '';
        $customer = $row['customer_name'] ?? '';
        $paymentMode = $row['payment_method'] ?? '';

        $feeTotal = $this->sanitizeNumber($row['fee'] ?? 0);
        $premiumTotal = $this->sanitizeNumber($row['premium'] ?? 0);

        $policyNumber = $row['policy_number'] ?? '';
        $saleAgent = $row['created_by_name'] ?? '';

        $description = $this->extractDescription($row);
        $amount = $this->extractGrandTotal($row);

        $feeSplitEnabled = $this->isTruthy($row['fee_split'] ?? false);
        $premiumSplitEnabled = $this->isTruthy($row['premium_split'] ?? false);

        if (!$feeSplitEnabled && !$premiumSplitEnabled) {
            return [[
                'payment_number' => $paymentNumber,
                'date'           => $this->formatDate($dateValue),
                'invoice_number' => $estimateNumber,
                'customer'       => $customer,
                'payment_mode'   => $paymentMode,
                'fee'            => $feeTotal,
                'premium'        => $premiumTotal,
                'policy_number'  => $policyNumber,
                'description'    => $description,
                'amount'         => $amount,
                'sale_agent'     => $saleAgent,
            ]];
        }

        $segments = [];

        if ($premiumSplitEnabled) {
            $premiumRows = $this->extractSplitRows($row, 'premium', $paymentMode, 'premium');

            if (empty($premiumRows) && $premiumTotal > 0) {
                $premiumRows[] = [
                    'payment_mode' => $paymentMode,
                    'fee' => 0,
                    'premium' => $premiumTotal,
                ];
            }

            $segments = array_merge($segments, $premiumRows);
        } elseif ($premiumTotal > 0) {
            $segments[] = [
                'payment_mode' => $paymentMode,
                'fee' => 0,
                'premium' => $premiumTotal,
            ];
        }

        if ($feeSplitEnabled) {
            $feeRows = $this->extractSplitRows($row, 'fee', $paymentMode, 'fee');

            if (empty($feeRows) && $feeTotal > 0) {
                $feeRows[] = [
                    'payment_mode' => $paymentMode,
                    'fee' => $feeTotal,
                    'premium' => 0,
                ];
            }

            $segments = array_merge($segments, $feeRows);
        } elseif ($feeTotal > 0) {
            $segments[] = [
                'payment_mode' => $paymentMode,
                'fee' => $feeTotal,
                'premium' => 0,
            ];
        }

        if (empty($segments)) {
            $segments[] = [
                'payment_mode' => $paymentMode,
                'fee' => $feeTotal,
                'premium' => $premiumTotal,
            ];
        }

        $reportRows = [];

        foreach ($segments as $index => $segment) {
            $reportRows[] = [
                'payment_number' => $paymentNumber,
                'date'           => $this->formatDate($dateValue),
                'invoice_number' => $estimateNumber,
                'customer'       => $customer,
                'payment_mode'   => $segment['payment_mode'] ?? $paymentMode,
                'fee'            => $segment['fee'] ?? 0,
                'premium'        => $segment['premium'] ?? 0,
                'policy_number'  => $policyNumber,
                'description'    => $description,
                'amount'         => $index === 0 ? $amount : 0,
                'sale_agent'     => $saleAgent,
            ];
        }

        return $reportRows;
    }
}
