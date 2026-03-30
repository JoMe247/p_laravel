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

    public function customersData(Request $request)
    {
        $authUser = Auth::guard('web')->user() ?? Auth::guard('sub')->user();
        if (!$authUser) {
            return response()->json(['columns' => [], 'rows' => []], 401);
        }

        $agency = $authUser->agency ?? null;

        $allColumns = Schema::getColumnListing('customers');

        $visibleColumns = array_values(array_filter($allColumns, function ($column) {
            return !in_array(strtolower($column), ['picture', 'alert'], true);
        }));

        $agencyColumn = $this->resolveFirstExistingColumn('customers', [
            'Agency',
            'agency',
        ]);

        $dateColumn = $this->resolveFirstExistingColumn('customers', [
            'Added',
            'added',
            'created_at',
        ]);

        $query = DB::table('customers');

        if ($agency && $agencyColumn) {
            $query->where($agencyColumn, $agency);
        }

        if ($dateColumn) {
            $this->applyGenericPeriodFilter(
                $query,
                $dateColumn,
                $request->get('period', 'all'),
                $request->get('from'),
                $request->get('to')
            );
        }

        if ($dateColumn) {
            $query->orderBy($dateColumn, 'desc');
        } elseif (in_array('ID', $allColumns, true)) {
            $query->orderBy('ID', 'desc');
        } elseif (in_array('id', $allColumns, true)) {
            $query->orderBy('id', 'desc');
        }

        $rows = [];
        foreach ($query->get() as $customer) {
            $data = (array) $customer;
            $row = [];

            foreach ($visibleColumns as $column) {
                $row[$column] = $data[$column] ?? '';
            }

            $rows[] = $row;
        }

        $columns = array_map(function ($column) {
            return [
                'key' => $column,
                'label' => $this->humanizeColumnName($column),
                'type' => 'text',
            ];
        }, $visibleColumns);

        return response()->json([
            'columns' => $columns,
            'rows' => $rows,
        ]);
    }


    public function itemsData(Request $request)
    {
        $authUser = Auth::guard('web')->user() ?? Auth::guard('sub')->user();
        if (!$authUser) {
            return response()->json(['columns' => [], 'rows' => []], 401);
        }

        $agency = $authUser->agency ?? null;
        $agentFilter = trim((string) $request->get('agent', ''));

        $dateColumn = $this->resolveFirstExistingColumn('invoices', [
            'payment_date',
            'creation_date',
            'created_at',
        ]) ?? 'created_at';

        $query = DB::table('invoices')->select([
            'inv_prices',
            'created_by_name',
        ]);

        if ($agency && Schema::hasColumn('invoices', 'agency')) {
            $query->where('agency', $agency);
        }

        if ($agentFilter !== '' && Schema::hasColumn('invoices', 'created_by_name')) {
            $query->where('created_by_name', 'like', '%' . $agentFilter . '%');
        }

        if ($dateColumn && Schema::hasColumn('invoices', $dateColumn)) {
            $this->applyGenericPeriodFilter(
                $query,
                $dateColumn,
                $request->get('period', 'all'),
                $request->get('from'),
                $request->get('to')
            );
        }

        $catalog = $this->getInvoiceItemsCatalog();

        $stats = [];
        foreach ($catalog as $itemName) {
            $stats[$itemName] = [
                'item_name' => $itemName,
                'item_count' => 0,
                'item_total_amount' => 0,
                'item_average' => 0,
            ];
        }

        foreach ($query->get() as $invoice) {
            if (empty($invoice->inv_prices)) {
                continue;
            }

            $decoded = json_decode($invoice->inv_prices, true);
            if (json_last_error() !== JSON_ERROR_NONE || !is_array($decoded)) {
                continue;
            }

            foreach (($decoded['rows'] ?? []) as $detail) {
                $itemName = trim((string) ($detail['item'] ?? $detail['description'] ?? ''));
                if ($itemName === '' || !array_key_exists($itemName, $stats)) {
                    continue;
                }

                $qty = $this->sanitizeNumber($detail['amount'] ?? $detail['qty'] ?? 1);
                if ($qty <= 0) {
                    $qty = 1;
                }

                $rowTotal = 0;
                if (isset($detail['row_total'])) {
                    $rowTotal = $this->sanitizeNumber($detail['row_total']);
                } elseif (isset($detail['total'])) {
                    $rowTotal = $this->sanitizeNumber($detail['total']);
                } else {
                    $price = $this->sanitizeNumber($detail['price'] ?? 0);
                    $rowTotal = $qty * $price;
                }

                $stats[$itemName]['item_count'] += $qty;
                $stats[$itemName]['item_total_amount'] += $rowTotal;
            }
        }

        foreach ($stats as &$itemRow) {
            $itemRow['item_average'] = $itemRow['item_count'] > 0
                ? ($itemRow['item_total_amount'] / $itemRow['item_count'])
                : 0;
        }
        unset($itemRow);

        return response()->json([
            'columns' => [
                ['key' => 'item_name', 'label' => 'Items', 'type' => 'text'],
                ['key' => 'item_count', 'label' => 'Amount', 'type' => 'number'],
                ['key' => 'item_total_amount', 'label' => 'Total Amount', 'type' => 'money'],
                ['key' => 'item_average', 'label' => 'Promedio', 'type' => 'money'],
            ],
            'rows' => array_values($stats),
        ]);
    }

    private function applyGenericPeriodFilter($query, string $dateColumn, string $period, ?string $from, ?string $to): void
    {
        $now = \Carbon\Carbon::now();

        switch ($period) {
            case 'this_month':
                $query->whereBetween($dateColumn, [
                    $now->copy()->startOfMonth(),
                    $now->copy()->endOfMonth(),
                ]);
                break;

            case 'last_month':
                $query->whereBetween($dateColumn, [
                    $now->copy()->subMonthNoOverflow()->startOfMonth(),
                    $now->copy()->subMonthNoOverflow()->endOfMonth(),
                ]);
                break;

            case 'this_year':
                $query->whereBetween($dateColumn, [
                    $now->copy()->startOfYear(),
                    $now->copy()->endOfYear(),
                ]);
                break;

            case 'last_year':
                $query->whereBetween($dateColumn, [
                    $now->copy()->subYear()->startOfYear(),
                    $now->copy()->subYear()->endOfYear(),
                ]);
                break;

            case 'last_3_months':
                $query->whereBetween($dateColumn, [
                    $now->copy()->subMonthsNoOverflow(2)->startOfMonth(),
                    $now->copy()->endOfMonth(),
                ]);
                break;

            case 'last_6_months':
                $query->whereBetween($dateColumn, [
                    $now->copy()->subMonthsNoOverflow(5)->startOfMonth(),
                    $now->copy()->endOfMonth(),
                ]);
                break;

            case 'last_12_months':
                $query->whereBetween($dateColumn, [
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

                    $query->whereBetween($dateColumn, [$fromDate, $toDate]);
                }
                break;
        }
    }

    private function humanizeColumnName(string $column): string
    {
        $label = preg_replace('/([a-zA-Z])(\d)/', '$1 $2', $column);
        $label = preg_replace('/([a-z])([A-Z])/', '$1 $2', $label);
        $label = str_replace(['_', '-'], ' ', $label);
        $label = preg_replace('/\s+/', ' ', trim($label));
        $label = ucwords(strtolower($label));

        $replacements = [
            'Id' => 'ID',
            'Zip Code' => 'ZIP Code',
            'Dob' => 'DOB',
            'Dl' => 'DL',
            'Dl State' => 'DL State',
            'Cid' => 'CID',
        ];

        return $replacements[$label] ?? $label;
    }

    private function getInvoiceItemsCatalog(): array
    {
        return [
            'Add Coverage - Comp & Collision Or Umbi/umpd',
            'Add Driver',
            'Add Vehicle W/comp & Collision Or Umbi/umpd',
            'Add Vehicle W/liability',
            'Address Change',
            'Certificate Of Insurance',
            'Credit Card Fee',
            'Delete Vehicle',
            'Exclude Driver',
            'Installment',
            'Installment Fee',
            'Late Fee',
            'New Business Commercial',
            'New Business Comp & Collision Or Umbi/umpd W/dl',
            'New Business Comp & Collision Or Umbi/umpd W/out Dl',
            'New Business General Liability',
            'New Business Homeowners',
            'New Business Liability W/dl',
            'New Business Liability W/out Dl',
            'New Business Motorcycle',
            'New Business Renters W/lemonade',
            'New Business Renters W/progressive',
            'New Business Sr22 Suspended Dl',
            'Notary',
            'Nsf Fee',
            'Other',
            'Reinstate',
            'Renewal Fee - 12 Months',
            'Renewal Fee - 2 Months',
            'Renewal Fee - 3 Months',
            'Renewal Fee - 6 Months',
            'Rewrite Comp & Collision Or Umbi/umpd',
            'Rewrite Liability',
            'Sr-22',
            'Swap Drivers',
            'Swap Vehicle',
        ];
    }

    public function policiesData(Request $request)
    {
        $authUser = Auth::guard('web')->user() ?? Auth::guard('sub')->user();
        if (!$authUser) {
            return response()->json(['columns' => [], 'rows' => []], 401);
        }

        $agency = $authUser->agency ?? null;

        /*
    |--------------------------------------------------------------------------
    | Actualiza remaining_time y last_payment cada vez que se carga el reporte
    |--------------------------------------------------------------------------
    */
        $this->syncPoliciesDerivedFields($agency);

        $allColumns = Schema::getColumnListing('policies');

        $dateColumn = $this->resolveFirstExistingColumn('policies', [
            'pol_added_date',
            'created_at',
            'pol_eff_date',
            'pol_expiration',
        ]);

        $agencyColumn = $this->resolveFirstExistingColumn('policies', [
            'agency',
            'Agency',
        ]);

        $query = DB::table('policies as p');

        if (in_array('customer_id', $allColumns, true)) {
            $query->leftJoin('customers as c', 'c.ID', '=', 'p.customer_id')
                ->select('p.*', DB::raw('COALESCE(c.Name, "") as customer_name'));
        } else {
            $query->select('p.*');
        }

        if ($agency && $agencyColumn) {
            $query->where("p.$agencyColumn", $agency);
        }

        if ($dateColumn) {
            $this->applyGenericPeriodFilter(
                $query,
                "p.$dateColumn",
                $request->get('period', 'all'),
                $request->get('from'),
                $request->get('to')
            );
        }

        if ($dateColumn) {
            $query->orderBy("p.$dateColumn", 'desc');
        } elseif (in_array('id', $allColumns, true)) {
            $query->orderBy('p.id', 'desc');
        } elseif (in_array('ID', $allColumns, true)) {
            $query->orderBy('p.ID', 'desc');
        }

        /*
    |--------------------------------------------------------------------------
    | Definición de columnas visibles
    | - customer_id -> Customer
    | - vehicules   -> Vehicles
    |--------------------------------------------------------------------------
    */
        $columns = [];
        foreach ($allColumns as $column) {
            $lower = strtolower($column);

            if (in_array($lower, ['created_at', 'updated_at', 'last_payment'], true)) {
                continue;
            }

            if ($lower === 'customer_id') {
                $columns[] = [
                    'key' => 'customer_name',
                    'label' => 'Customer',
                    'type' => 'text',
                ];
                continue;
            }

            if ($lower === 'vehicules') {
                $columns[] = [
                    'key' => 'vehicles_count',
                    'label' => 'Vehicles',
                    'type' => 'number',
                ];
                continue;
            }

            $columns[] = [
                'key' => $column,
                'label' => $this->getPoliciesColumnLabel($column),
                'type' => 'text',
            ];
        }

        $rows = [];
        foreach ($query->get() as $policy) {
            $data = (array) $policy;
            $data['vehicles_count'] = $this->countVehiclesFromJson($data['vehicules'] ?? null);

            $row = [];
            foreach ($columns as $column) {
                $row[$column['key']] = $data[$column['key']] ?? '';
            }

            $rows[] = $row;
        }

        return response()->json([
            'columns' => $columns,
            'rows' => $rows,
        ]);
    }

    private function syncPoliciesDerivedFields(?string $agency): void
    {
        if (!Schema::hasTable('policies')) {
            return;
        }

        $policyIdColumn = $this->resolveFirstExistingColumn('policies', ['id', 'ID']);
        $policyNumberColumn = $this->resolveFirstExistingColumn('policies', ['pol_number', 'policy_number']);
        $agencyColumn = $this->resolveFirstExistingColumn('policies', ['agency', 'Agency']);
        $expirationColumn = $this->resolveFirstExistingColumn('policies', ['pol_expiration']);
        $customerIdColumn = $this->resolveFirstExistingColumn('policies', ['customer_id']);

        if (!$policyIdColumn || !$policyNumberColumn) {
            return;
        }

        $policiesQuery = DB::table('policies')->select([
            $policyIdColumn . ' as policy_pk',
            $policyNumberColumn . ' as policy_number',
        ]);

        if ($expirationColumn) {
            $policiesQuery->addSelect($expirationColumn . ' as policy_expiration');
        }

        if ($customerIdColumn) {
            $policiesQuery->addSelect($customerIdColumn . ' as policy_customer_id');
        }

        if (Schema::hasColumn('policies', 'remaining_time')) {
            $policiesQuery->addSelect('remaining_time as current_remaining_time');
        }

        if (Schema::hasColumn('policies', 'last_payment')) {
            $policiesQuery->addSelect('last_payment as current_last_payment');
        }

        if ($agency && $agencyColumn) {
            $policiesQuery->where($agencyColumn, $agency);
        }

        $policies = $policiesQuery->get();

        /*
    |--------------------------------------------------------------------------
    | Mapa del último pago por policy_number (+ customer_id cuando exista)
    |--------------------------------------------------------------------------
    */
        $invoiceQuery = DB::table('invoices')->select([
            'policy_number',
            'customer_id',
            'inv_prices',
        ]);

        if ($agency && Schema::hasColumn('invoices', 'agency')) {
            $invoiceQuery->where('agency', $agency);
        }

        if (Schema::hasColumn('invoices', 'created_at')) {
            $invoiceQuery->orderBy('created_at', 'desc');
        } elseif (Schema::hasColumn('invoices', 'creation_date')) {
            $invoiceQuery->orderBy('creation_date', 'desc');
        } elseif (Schema::hasColumn('invoices', 'payment_date')) {
            $invoiceQuery->orderBy('payment_date', 'desc');
        } else {
            $invoiceQuery->orderBy('id', 'desc');
        }

        $latestPayments = [];

        foreach ($invoiceQuery->get() as $invoice) {
            $policyNumber = trim((string) ($invoice->policy_number ?? ''));
            if ($policyNumber === '') {
                continue;
            }

            $grandTotal = $this->extractGrandTotal([
                'inv_prices' => $invoice->inv_prices,
            ]);

            $customerId = trim((string) ($invoice->customer_id ?? ''));
            $keyByPolicyAndCustomer = $policyNumber . '|' . $customerId;
            $keyByPolicyOnly = $policyNumber;

            if (!array_key_exists($keyByPolicyAndCustomer, $latestPayments)) {
                $latestPayments[$keyByPolicyAndCustomer] = $grandTotal;
            }

            if (!array_key_exists($keyByPolicyOnly, $latestPayments)) {
                $latestPayments[$keyByPolicyOnly] = $grandTotal;
            }
        }

        foreach ($policies as $policy) {
            $updates = [];

            if (Schema::hasColumn('policies', 'remaining_time')) {
                $newRemainingTime = $this->getRemainingTimeLabel($policy->policy_expiration ?? null);
                $currentRemainingTime = (string) ($policy->current_remaining_time ?? '');

                if ($newRemainingTime !== $currentRemainingTime) {
                    $updates['remaining_time'] = $newRemainingTime;
                }
            }

            if (Schema::hasColumn('policies', 'last_payment')) {
                $policyNumber = trim((string) ($policy->policy_number ?? ''));
                $policyCustomerId = trim((string) ($policy->policy_customer_id ?? ''));

                $paymentKey1 = $policyNumber . '|' . $policyCustomerId;
                $paymentKey2 = $policyNumber;

                $newLastPayment = null;
                if (array_key_exists($paymentKey1, $latestPayments)) {
                    $newLastPayment = $latestPayments[$paymentKey1];
                } elseif (array_key_exists($paymentKey2, $latestPayments)) {
                    $newLastPayment = $latestPayments[$paymentKey2];
                }

                $newLastPaymentValue = $newLastPayment !== null ? number_format((float) $newLastPayment, 2, '.', '') : null;
                $currentLastPayment = $policy->current_last_payment ?? null;

                if ((string) $newLastPaymentValue !== (string) $currentLastPayment) {
                    $updates['last_payment'] = $newLastPaymentValue;
                }
            }

            if (!empty($updates)) {
                DB::table('policies')
                    ->where($policyIdColumn, $policy->policy_pk)
                    ->update($updates);
            }
        }
    }

    private function getRemainingTimeLabel($expirationDate): string
    {
        $expiration = $this->parseFlexibleDate($expirationDate);

        if (!$expiration) {
            return '';
        }

        $today = Carbon::today();
        $expiration = $expiration->copy()->startOfDay();

        if ($expiration->lt($today)) {
            return 'Expired';
        }

        if ($expiration->equalTo($today)) {
            return 'Expires today';
        }

        $cursor = $today->copy();

        $years = 0;
        while ($cursor->copy()->addYear()->lte($expiration)) {
            $cursor->addYear();
            $years++;
        }

        $months = 0;
        while ($cursor->copy()->addMonth()->lte($expiration)) {
            $cursor->addMonth();
            $months++;
        }

        $days = $cursor->diffInDays($expiration);

        if ($years > 0) {
            $parts = [];
            $parts[] = $years . ' ' . ($years === 1 ? 'year' : 'years');

            if ($months > 0) {
                $parts[] = $months . ' ' . ($months === 1 ? 'month' : 'months');
            }

            return implode(' ', $parts) . ' remaining';
        }

        if ($months > 0) {
            return $months . ' ' . ($months === 1 ? 'month' : 'months') . ' remaining';
        }

        if ($days >= 7) {
            $weeks = intdiv($days, 7);
            $remainingDays = $days % 7;

            $parts = [];
            $parts[] = $weeks . ' ' . ($weeks === 1 ? 'week' : 'weeks');

            if ($remainingDays > 0) {
                $parts[] = $remainingDays . ' ' . ($remainingDays === 1 ? 'day' : 'days');
            }

            return implode(' ', $parts) . ' remaining';
        }

        return $days . ' ' . ($days === 1 ? 'day' : 'days') . ' remaining';
    }

    private function parseFlexibleDate($value): ?Carbon
    {
        if (!$value) {
            return null;
        }

        $value = trim((string) $value);

        $formats = [
            'd/m/Y',
            'd-m-Y',
            'Y-m-d',
            'Y/m/d',
            'm/d/Y',
            'm-d-Y',
            'd/m/Y H:i:s',
            'd-m-Y H:i:s',
            'Y-m-d H:i:s',
            'Y/m/d H:i:s',
            'm/d/Y H:i:s',
            'm-d-Y H:i:s',
        ];

        foreach ($formats as $format) {
            try {
                return Carbon::createFromFormat($format, $value);
            } catch (\Throwable $e) {
            }
        }

        try {
            return Carbon::parse($value);
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function countVehiclesFromJson($value): int
    {
        if (!$value) {
            return 0;
        }

        $decoded = json_decode($value, true);

        if (json_last_error() !== JSON_ERROR_NONE || !is_array($decoded)) {
            return 0;
        }

        if (array_is_list($decoded)) {
            return count($decoded);
        }

        foreach (['vehicles', 'vehicules', 'items', 'data'] as $key) {
            if (isset($decoded[$key]) && is_array($decoded[$key])) {
                return count($decoded[$key]);
            }
        }

        return !empty($decoded) ? 1 : 0;
    }

    private function getPoliciesColumnLabel(string $column): string
    {
        $map = [
            'id' => 'ID',
            'customer_id' => 'Customer',
            'pol_number' => 'Policy #',
            'pol_carrier' => 'Carrier',
            'pol_expiration' => 'Expiration',
            'remaining_time' => 'Remaining Time',
            'last_payment' => 'Last Payment',
            'pol_status' => 'Status',
            'pol_url' => 'Policy URL',
            'pol_eff_date' => 'Effective Date',
            'pol_added_date' => 'Added Date',
            'pol_due_day' => 'Due Day',
            'pol_agent_record' => 'Agent Of Record',
            'vehicules' => 'Vehicles',
        ];

        if (isset($map[$column])) {
            return $map[$column];
        }

        return $this->humanizeColumnName($column);
    }
}
