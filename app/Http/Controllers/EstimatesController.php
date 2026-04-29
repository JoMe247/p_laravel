<?php

namespace App\Http\Controllers;

use App\Models\Estimates;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class EstimatesController extends Controller
{
    // =========================
    // LISTA
    // =========================
    public function estimates($customerId)
    {
        $authUser = Auth::guard('web')->user() ?? Auth::guard('sub')->user();
        if (!$authUser) return redirect()->route('login');

        $agency = $authUser->agency;

        $customer = DB::table('customers')->where('ID', $customerId)->first();
        if (!$customer) abort(404, 'Customer no encontrado');

        $agencyInfo = DB::table('agency')->where('agency_code', $agency)->first();

        $estimates = Estimates::where('agency', (string)$agency)
            ->where('customer_id', (string)$customerId)
            ->orderBy('created_at', 'desc')
            ->paginate(30);

        $estimates->getCollection()->transform(function ($est) {
            $amount = '';
            $firstItem = '';

            if (!empty($est->inv_prices)) {
                $decoded = json_decode($est->inv_prices, true);
                if (is_array($decoded)) {
                    $amount = $decoded['grand_total'] ?? '';
                    $rows = $decoded['rows'] ?? [];
                    if (is_array($rows) && count($rows) > 0) {
                        $firstItem = $rows[0]['item'] ?? '';
                    }
                }
            }

            $est->amount_calc = $amount;
            $est->first_item = $firstItem;
            return $est;
        });

        return view('estimates', [
            'customerId' => $customerId,
            'agency' => $agency,
            'customer' => $customer,
            'estimates' => $estimates,
            'agencyInfo' => $agencyInfo,
        ]);
    }

    // =========================
    // REGISTRO (CLON de invoices)
    // =========================
    public function register($customerId)
    {
        $authUser = Auth::guard('web')->user() ?? Auth::guard('sub')->user();
        if (!$authUser) return redirect()->route('login');

        $agency = (string) $authUser->agency;

        $agencyInfo = DB::table('agency')
            ->where('agency_code', $agency)
            ->first();

        $agencyInfo = $agencyInfo ?: (object)[
            'agency_code'    => $agency,
            'agency_name'    => '',
            'office_phone'   => '',
            'agency_address' => '',
            'agency_logo'    => '',
        ];

        $customer = DB::table('customers')->where('ID', $customerId)->first();
        if (!$customer) abort(404, 'Customer no encontrado');

        $estimateIdParam = request('estimateId');

        if (!empty($estimateIdParam)) {
            // Abrir por ID
            $meta = Estimates::where('id', (string)$estimateIdParam)
                ->where('agency', (string)$agency)
                ->where('customer_id', (string)$customerId)
                ->first();

            if (!$meta) abort(404, 'Estimate not found');
        } else {
            $new = request()->boolean('new'); // true si ?new=1

            if ($new) {
                // ✅ Crear estimate nuevo (vacío) con consecutivo EST-0001 por agency
                $estimate = new Estimates();
                $estimate->id = (string) Str::uuid();
                $estimate->agency = (string)$agency;
                $estimate->customer_id = (string)$customerId;
                $estimate->created_at = now()->format('Y-m-d H:i:s');
                $estimate->updated_at = now()->format('Y-m-d H:i:s');
                $estimate->created_by_name = $authUser->name ?? '';

                $next = DB::transaction(function () use ($agency) {
                    $last = DB::table('estimates')
                        ->where('agency', (string)$agency)
                        ->whereNotNull('estimate_number')
                        ->orderBy('estimate_number', 'desc')
                        ->lockForUpdate()
                        ->value('estimate_number');

                    $num = 0;
                    if ($last && preg_match('/^EST-(\d+)$/', $last, $m)) {
                        $num = (int) $m[1];
                    }

                    $num++;
                    return 'EST-' . str_pad((string)$num, 4, '0', STR_PAD_LEFT);
                });

                $estimate->estimate_number = $next;

                $today = now()->format('Y-m-d');
                $estimate->creation_date = $today;
                $estimate->payment_date  = $today;

                // si tu DB requiere policy_number NOT NULL, inicialízalo vacío
                $estimate->policy_number = $estimate->policy_number ?? '';

                $estimate->save();

                $meta = $estimate;
            } else {
                // Abrir el último si existe
                $meta = Estimates::where('agency', (string)$agency)
                    ->where('customer_id', (string)$customerId)
                    ->orderBy('created_at', 'desc')
                    ->first();
            }
        }

        $estimateId = $meta->id ?? '';
        $estimateNumber = $meta->estimate_number ?? '';

        // Policies
        $policiesCount = DB::table('policies')->where('customer_id', $customerId)->count();
        $policyNumbers = DB::table('policies')
            ->where('customer_id', $customerId)
            ->orderBy('id', 'asc')
            ->pluck('pol_number')
            ->filter()
            ->values();

        // Rows/GrandTotal
        $invRows = [];
        $grandTotalSaved = '';
        if ($meta && !empty($meta->inv_prices)) {
            $decoded = json_decode($meta->inv_prices, true);
            if (is_array($decoded)) {
                $invRows = $decoded['rows'] ?? [];
                $grandTotalSaved = $decoded['grand_total'] ?? '';
            }
        }

        // Dates + Charges
        $nextPyDate = $meta->next_py_date ?? '';
        $creationDate = $meta->creation_date ?? '';
        $paymentDate  = $meta->payment_date ?? '';

        $fee                 = $meta->fee ?? '';
        $feeSplit            = ($meta->fee_split ?? '') === '1';
        $feeP1Method         = $meta->fee_payment1_method ?? '';
        $feeP1Value          = $meta->fee_payment1_value ?? '';
        $feeP2Method         = $meta->fee_payment2_method ?? '';
        $feeP2Value          = $meta->fee_payment2_value ?? '';

        $premium             = $meta->premium ?? '';
        $premiumSplit        = ($meta->premium_split ?? '') === '1';
        $premiumP1Method     = $meta->premium_payment1_method ?? '';
        $premiumP1Value      = $meta->premium_payment1_value ?? '';
        $premiumP2Method     = $meta->premium_payment2_method ?? '';
        $premiumP2Value      = $meta->premium_payment2_value ?? '';

        return view('estimate_register', compact(
            'customerId',
            'agency',
            'agencyInfo',
            'customer',
            'policiesCount',
            'policyNumbers',
            'nextPyDate',
            'creationDate',
            'paymentDate',
            'fee',
            'feeSplit',
            'feeP1Method',
            'feeP1Value',
            'feeP2Method',
            'feeP2Value',
            'premium',
            'premiumSplit',
            'premiumP1Method',
            'premiumP1Value',
            'premiumP2Method',
            'premiumP2Value',
            'invRows',
            'grandTotalSaved',
            'estimateId',
            'estimateNumber'
        ));
    }

    // =========================
    // SAVE DATES
    // =========================
    public function saveDates(Request $request, $customerId)
    {
        $authUser = Auth::guard('web')->user() ?? Auth::guard('sub')->user();
        if (!$authUser) return response()->json(['ok' => false, 'error' => 'not_auth'], 401);

        $agency = $authUser->agency;

        $estimateId = (string) $request->input('estimate_id', '');
        if (empty($estimateId)) {
            return response()->json(['ok' => false, 'error' => 'missing_estimate_id'], 422);
        }

        $data = $request->validate([
            'next_py_date'  => 'nullable|string|max:30',
            'creation_date' => 'nullable|string|max:30',
            'payment_date'  => 'nullable|string|max:30',
        ]);

        $updated = Estimates::where('id', $estimateId)
            ->where('agency', (string)$agency)
            ->where('customer_id', (string)$customerId)
            ->update([
                'next_py_date'   => $data['next_py_date'] ?? '',
                'creation_date'  => $data['creation_date'] ?? '',
                'payment_date'   => $data['payment_date'] ?? '',
                'updated_at'     => now()->format('Y-m-d H:i:s'),
            ]);

        return response()->json(['ok' => true, 'updated' => $updated]);
    }

    // =========================
    // SAVE CHARGES
    // =========================
    public function saveCharges(Request $request, $customerId)
    {
        $authUser = Auth::guard('web')->user() ?? Auth::guard('sub')->user();
        if (!$authUser) return response()->json(['ok' => false, 'error' => 'not_auth'], 401);

        $agency = $authUser->agency;

        $estimateId = (string) $request->input('estimate_id', '');
        if (empty($estimateId)) {
            return response()->json(['ok' => false, 'error' => 'missing_estimate_id'], 422);
        }

        $data = $request->validate([
            'fee' => 'nullable|string|max:50',
            'fee_split' => 'nullable|string|max:10',
            'fee_payment1_method' => 'nullable|string|max:30',
            'fee_payment1_value' => 'nullable|string|max:50',
            'fee_payment2_method' => 'nullable|string|max:30',
            'fee_payment2_value' => 'nullable|string|max:50',

            'premium' => 'nullable|string|max:50',
            'premium_split' => 'nullable|string|max:10',
            'premium_payment1_method' => 'nullable|string|max:30',
            'premium_payment1_value' => 'nullable|string|max:50',
            'premium_payment2_method' => 'nullable|string|max:30',
            'premium_payment2_value' => 'nullable|string|max:50',
        ]);

        $updated = Estimates::where('id', $estimateId)
            ->where('agency', (string)$agency)
            ->where('customer_id', (string)$customerId)
            ->update(array_merge($data, [
                'updated_at' => now()->format('Y-m-d H:i:s'),
            ]));

        return response()->json(['ok' => true, 'updated' => $updated]);
    }

    // =========================
    // SAVE TABLE JSON (CLON de invoices)
    // =========================
    public function saveEstimateTable(Request $request, $customerId)
    {
        $authUser = Auth::guard('web')->user() ?? Auth::guard('sub')->user();
        if (!$authUser) return response()->json(['ok' => false, 'error' => 'not_auth'], 401);

        $agency = (string) $authUser->agency;

        $rows = $request->input('rows', []);
        $grandTotal = $request->input('grand_total', '');
        $policyNumber = $request->input('policy_number', '');
        $estimateIdFromClient = (string) $request->input('estimate_id', '');

        if (!is_array($rows)) $rows = [];

        $payload = [
            'rows' => $rows,
            'grand_total' => $grandTotal,
            'saved_at' => now()->format('Y-m-d H:i:s'),
        ];

        // 1) Intentar usar estimate_id enviado por el cliente
        $estimate = null;
        if (!empty($estimateIdFromClient)) {
            $estimate = Estimates::where('id', (string)$estimateIdFromClient)
                ->where('agency', (string)$agency)
                ->where('customer_id', (string)$customerId)
                ->first();
        }

        // 2) Si no hay estimate actual, toma el más reciente
        if (!$estimate) {
            $estimate = Estimates::where('agency', (string)$agency)
                ->where('customer_id', (string)$customerId)
                ->orderBy('created_at', 'desc')
                ->first();
        }

        // 3) Si aún no existe, crea uno nuevo
        if (!$estimate) {
            $estimate = new Estimates();
            $estimate->id = (string) Str::uuid();
            $estimate->agency = (string)$agency;
            $estimate->customer_id = (string)$customerId;
            $estimate->created_at = now()->format('Y-m-d H:i:s');
            $estimate->created_by_name = $authUser->name ?? '';
        }

        // 4) Si NO tiene estimate_number, generar consecutivo EST-0001 por agency
        if (empty($estimate->estimate_number)) {
            $next = DB::transaction(function () use ($agency) {
                $last = DB::table('estimates')
                    ->where('agency', (string)$agency)
                    ->whereNotNull('estimate_number')
                    ->orderBy('estimate_number', 'desc')
                    ->lockForUpdate()
                    ->value('estimate_number');

                $num = 0;
                if ($last && preg_match('/^EST-(\d+)$/', $last, $m)) {
                    $num = (int)$m[1];
                }

                $num++;
                return 'EST-' . str_pad((string)$num, 4, '0', STR_PAD_LEFT);
            });

            $estimate->estimate_number = $next;

            $today = now()->format('Y-m-d');
            $estimate->creation_date = $estimate->creation_date ?: $today;
            $estimate->payment_date  = $estimate->payment_date ?: $today;
        }

        // 5) Guardar policy_number seleccionado
        $estimate->policy_number = (string)$policyNumber;

        // 6) Guardar JSON tabla
        $estimate->inv_prices = json_encode($payload, JSON_UNESCAPED_UNICODE);
        $estimate->updated_at = now()->format('Y-m-d H:i:s');
        $estimate->save();

        return response()->json([
            'ok' => true,
            'estimate_id' => $estimate->id,
            'estimate_number' => $estimate->estimate_number,
            'policy_number' => $estimate->policy_number,
        ]);
    }

    // =========================
    // DELETE
    // =========================
    public function destroy($estimateId)
    {
        $authUser = Auth::guard('web')->user() ?? Auth::guard('sub')->user();
        if (!$authUser) return redirect()->route('login');

        $agency = $authUser->agency;

        Estimates::where('id', (string)$estimateId)
            ->where('agency', (string)$agency)
            ->delete();

        return back()->with('success', 'Estimate deleted');
    }

    // =========================
    // FOOTER IMAGE
    // =========================
    public function uploadEstimateFooterImage(Request $request)
    {
        $authUser = Auth::guard('web')->user() ?? Auth::guard('sub')->user();
        if (!$authUser) return response()->json(['ok' => false], 401);

        $agency = (string) $authUser->agency;

        $request->validate([
            'footer_image' => 'required|image|mimes:jpg,jpeg,png,webp|max:4096',
        ]);

        $agencyRow = DB::table('agency')->where('agency_code', $agency)->first();
        if (!$agencyRow) return response()->json(['ok' => false, 'error' => 'agency_not_found'], 404);

        if (!empty($agencyRow->estimate_footer_image)) {
            Storage::disk('public')->delete($agencyRow->estimate_footer_image);
        }

        $file = $request->file('footer_image');
        $name = 'estimate_footer_' . $agency . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs('estimate_footer_images/' . $agency, $name, 'public');

        DB::table('agency')
            ->where('agency_code', $agency)
            ->update([
                'estimate_footer_image' => $path,
                'estimate_footer_enabled' => 1,
            ]);

        return response()->json(['ok' => true, 'path' => $path]);
    }

    public function setEstimateFooterEnabled(Request $request)
    {
        $authUser = Auth::guard('web')->user() ?? Auth::guard('sub')->user();
        if (!$authUser) return response()->json(['ok' => false], 401);

        $agency = (string) $authUser->agency;

        $data = $request->validate([
            'enabled' => 'required|in:0,1',
        ]);

        DB::table('agency')
            ->where('agency_code', $agency)
            ->update([
                'estimate_footer_enabled' => (int)$data['enabled'],
            ]);

        return response()->json(['ok' => true]);
    }

    public function deleteEstimateFooterImage(Request $request)
    {
        $authUser = Auth::guard('web')->user() ?? Auth::guard('sub')->user();
        if (!$authUser) return response()->json(['ok' => false], 401);

        $agency = (string) $authUser->agency;

        $agencyRow = DB::table('agency')->where('agency_code', $agency)->first();
        if (!$agencyRow) return response()->json(['ok' => false, 'error' => 'agency_not_found'], 404);

        if (!empty($agencyRow->estimate_footer_image)) {
            Storage::disk('public')->delete($agencyRow->estimate_footer_image);
        }

        DB::table('agency')
            ->where('agency_code', $agency)
            ->update([
                'estimate_footer_image' => null,
                'estimate_footer_enabled' => 0,
            ]);

        return response()->json(['ok' => true]);
    }

    // =========================
    // PDF
    // =========================
    public function pdf($customerId, $estimateId)
    {
        $authUser = Auth::guard('web')->user() ?? Auth::guard('sub')->user();
        if (!$authUser) return redirect()->route('login');

        $agency = (string) $authUser->agency;

        $customer = DB::table('customers')->where('ID', $customerId)->first();
        if (!$customer) abort(404, 'Customer no encontrado');

        $agencyInfo = DB::table('agency')->where('agency_code', $agency)->first();
        if (!$agencyInfo) abort(404, 'Agency no encontrada');

        $estimate = Estimates::where('id', (string)$estimateId)
            ->where('agency', $agency)
            ->where('customer_id', (string)$customerId)
            ->first();

        if (!$estimate) abort(404, 'Estimate no encontrado');

        $rows = [];
        $grandTotal = 0;

        if (!empty($estimate->inv_prices)) {
            $decoded = json_decode($estimate->inv_prices, true);
            if (is_array($decoded)) {
                $rows = $decoded['rows'] ?? [];
                $grandTotal = (float) ($decoded['grand_total'] ?? 0);
            }
        }

        $pdf = Pdf::loadView('pdf.estimate_pdf', [
            'customer' => $customer,
            'agencyInfo' => $agencyInfo,
            'estimate' => $estimate,
            'rows' => $rows,
            'grandTotal' => $grandTotal,
        ])->setPaper('letter', 'portrait');

        $fileName = 'Estimate_' . ($estimate->estimate_number ?? $estimate->id) . '.pdf';
        return $pdf->download($fileName);
    }
}
