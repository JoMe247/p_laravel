<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Invoices;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class PaymentsInvoicesController extends Controller
{
    public function payments($customerId)
    {
        $authUser = Auth::guard('web')->user() ?? Auth::guard('sub')->user();
        if (!$authUser) return redirect()->route('login');

        $agency = $authUser->agency;
        $customer = DB::table('customers')->where('ID', $customerId)->first();
        if (!$customer) abort(404, 'Customer no encontrado');


        // ✅ últimos 30 invoices del customer (por agency)
        $invoices = Invoices::where('agency', (string)$agency)
            ->where('customer_id', (string)$customerId)
            ->orderBy('created_at', 'desc')
            ->paginate(30);

        // ✅ agregar columnas calculadas: amount + first_item
        $invoices->getCollection()->transform(function ($inv) {
            $amount = '';
            $firstItem = '';

            if (!empty($inv->inv_prices)) {
                $decoded = json_decode($inv->inv_prices, true);

                if (is_array($decoded)) {
                    $amount = $decoded['grand_total'] ?? '';

                    $rows = $decoded['rows'] ?? [];
                    if (is_array($rows) && count($rows) > 0) {
                        $firstItem = $rows[0]['item'] ?? '';
                    }
                }
            }

            $inv->amount_calc = $amount;
            $inv->first_item = $firstItem;

            return $inv;
        });

        return view('payments', [
            'customerId' => $customerId,
            'agency' => $agency,
            'customer' => $customer,
            'invoices' => $invoices,
        ]);
    }


    public function invoices($customerId)
    {
        $authUser = Auth::guard('web')->user() ?? Auth::guard('sub')->user();
        if (!$authUser) return redirect()->route('login');

        $agency = $authUser->agency; // MISMA agency donde subes logo

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

        $invoiceIdParam = request('invoiceId');

        if (!empty($invoiceIdParam)) {
            $meta = Invoices::where('id', (string)$invoiceIdParam)
                ->where('agency', (string)$agency)
                ->where('customer_id', (string)$customerId)
                ->first();

            if (!$meta) abort(404, 'Invoice not found');
        } else {

            $new = request()->boolean('new'); // true si ?new=1

            if ($new) {
                // Crear invoice nuevo (vacío) con consecutivo
                $invoice = new Invoices();
                $invoice->id = (string) \Illuminate\Support\Str::uuid();
                $invoice->agency = (string)$agency;
                $invoice->customer_id = (string)$customerId;
                $invoice->created_at = now()->format('Y-m-d H:i:s');
                $invoice->updated_at = now()->format('Y-m-d H:i:s');

                // Generar invoice_number INV-0001 por agency
                $next = DB::transaction(function () use ($agency) {
                    $last = DB::table('invoices')
                        ->where('agency', (string)$agency)
                        ->whereNotNull('invoice_number')
                        ->orderBy('invoice_number', 'desc')
                        ->lockForUpdate()
                        ->value('invoice_number');

                    $num = 0;
                    if ($last && preg_match('/^INV-(\d+)$/', $last, $m)) {
                        $num = (int)$m[1];
                    }
                    $num++;
                    return 'INV-' . str_pad((string)$num, 4, '0', STR_PAD_LEFT);
                });

                $invoice->invoice_number = $next;
                $today = now()->format('Y-m-d');
                $invoice->creation_date = $today;
                $invoice->payment_date  = $today;

                $invoice->save();

                // Este será el invoice "actual" en la vista
                $meta = $invoice;
            } else {
                // tu lógica actual (abrir el último)
                $meta = Invoices::where('agency', (string)$agency)
                    ->where('customer_id', (string)$customerId)
                    ->orderBy('created_at', 'desc')
                    ->first();
            }
        }


        $invoiceId = $meta->id ?? '';
        $invoiceNumber = $meta->invoice_number ?? '';

        $policiesCount = DB::table('policies')
            ->where('customer_id', $customerId)
            ->count();

        $policyNumbers = DB::table('policies')
            ->where('customer_id', $customerId)
            ->orderBy('id', 'asc')
            ->pluck('pol_number')
            ->filter()
            ->values();


        $invoiceMeta = Invoices::where('agency', (string)$agency)
            ->where('customer_id', (string)$customerId)
            ->orderBy('created_at', 'desc')
            ->first();

        $creationDate = $invoiceMeta->creation_date ?? '';
        $paymentDate  = $invoiceMeta->payment_date ?? '';

        $meta = Invoices::where('agency', (string)$agency)
            ->where('customer_id', (string)$customerId)
            ->orderBy('created_at', 'desc')
            ->first();

        $invoiceId = $meta->id ?? '';
        $invoiceNumber = $meta->invoice_number ?? '';


        $invRows = [];
        $grandTotalSaved = '';

        if ($meta && !empty($meta->inv_prices)) {
            $decoded = json_decode($meta->inv_prices, true);

            if (is_array($decoded)) {
                $invRows = $decoded['rows'] ?? [];
                $grandTotalSaved = $decoded['grand_total'] ?? '';
            }
        }


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



        return view('invoices', compact(
            'customerId',
            'agency',
            'agencyInfo',
            'customer',
            'policiesCount',

            'policyNumbers',
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
            'invoiceId',
            'invoiceNumber',
            'invoiceId',
            'invoiceNumber',
        ));
    }


    public function storeRow(Request $request, $customerId)
    {
        $agency = session('agency');

        $data = $request->validate([
            'item'   => 'nullable|string|max:255',
            'amount'   => 'nullable|string|max:255',
            'price'  => 'nullable|string|max:50',
        ]);

        $id  = (string) \Illuminate\Support\Str::uuid();
        $now = now()->format('Y-m-d H:i:s');

        Invoices::create([
            'id'         => $id,
            'agency'     => (string)$agency,
            'customer_id' => (string)$customerId,
            'item'      => $data['item'] ?? '',
            'amount'      => $data['amount'] ?? '',
            'price'     => $data['price'] ?? '',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        return response()->json(['ok' => true, 'id' => $id]);
    }

    public function saveDates(Request $request, $customerId)
    {
        $authUser = \Illuminate\Support\Facades\Auth::guard('web')->user()
            ?? \Illuminate\Support\Facades\Auth::guard('sub')->user();

        if (!$authUser) return response()->json(['ok' => false, 'error' => 'not_auth'], 401);

        $agency = $authUser->agency;

        $invoiceId = (string) $request->input('invoice_id', '');
        if (empty($invoiceId)) {
            return response()->json(['ok' => false, 'error' => 'missing_invoice_id'], 422);
        }

        $data = $request->validate([
            'creation_date' => 'nullable|string|max:30',
            'payment_date'  => 'nullable|string|max:30',
        ]);

        $updated = Invoices::where('id', $invoiceId)
            ->where('agency', (string)$agency)
            ->where('customer_id', (string)$customerId)
            ->update([
                'creation_date' => $data['creation_date'] ?? '',
                'payment_date'  => $data['payment_date'] ?? '',
                'updated_at'    => now()->format('Y-m-d H:i:s'),
            ]);

        return response()->json(['ok' => true, 'updated' => $updated]);
    }


    public function saveCharges(Request $request, $customerId)
    {
        $authUser = \Illuminate\Support\Facades\Auth::guard('web')->user()
            ?? \Illuminate\Support\Facades\Auth::guard('sub')->user();

        if (!$authUser) return response()->json(['ok' => false, 'error' => 'not_auth'], 401);

        $agency = $authUser->agency;

        // ✅ invoice_id separado (NO dentro de $data para update)
        $invoiceId = (string) $request->input('invoice_id', '');

        if (empty($invoiceId)) {
            return response()->json(['ok' => false, 'error' => 'missing_invoice_id'], 422);
        }

        // ✅ valida SOLO los campos de charges (sin invoice_id)
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

        $updated = Invoices::where('id', $invoiceId)
            ->where('agency', (string)$agency)
            ->where('customer_id', (string)$customerId)
            ->update(array_merge($data, [
                'updated_at' => now()->format('Y-m-d H:i:s'),
            ]));

        return response()->json(['ok' => true, 'updated' => $updated]);
    }

    public function saveInvoiceTable(Request $request, $customerId)
    {
        $authUser = \Illuminate\Support\Facades\Auth::guard('web')->user()
            ?? \Illuminate\Support\Facades\Auth::guard('sub')->user();

        if (!$authUser) return response()->json(['ok' => false, 'error' => 'not_auth'], 401);

        $agency = $authUser->agency;

        $rows = $request->input('rows', []);
        $grandTotal = $request->input('grand_total', '');
        $policyNumber = $request->input('policy_number', '');
        $invoiceIdFromClient = $request->input('invoice_id', '');

        if (!is_array($rows)) $rows = [];

        $payload = [
            'rows' => $rows,
            'grand_total' => $grandTotal,
            'saved_at' => now()->format('Y-m-d H:i:s'),
        ];

        // 1) Intentar usar el invoice_id enviado por el cliente
        $invoice = null;
        if (!empty($invoiceIdFromClient)) {
            $invoice = Invoices::where('id', (string)$invoiceIdFromClient)
                ->where('agency', (string)$agency)
                ->where('customer_id', (string)$customerId)
                ->first();
        }

        // 2) Si no hay invoice actual, toma el más reciente
        if (!$invoice) {
            $invoice = Invoices::where('agency', (string)$agency)
                ->where('customer_id', (string)$customerId)
                ->orderBy('created_at', 'desc')
                ->first();
        }

        // 3) Si aún no existe, crea uno nuevo
        if (!$invoice) {
            $invoice = new Invoices();
            $invoice->id = (string) \Illuminate\Support\Str::uuid();
            $invoice->agency = (string)$agency;
            $invoice->customer_id = (string)$customerId;
            $invoice->created_at = now()->format('Y-m-d H:i:s');
        }

        // 4) Si NO tiene invoice_number, generar consecutivo INV-0001 por agency
        if (empty($invoice->invoice_number)) {
            $next = DB::transaction(function () use ($agency) {
                // Tomamos el último consecutivo por agency
                $last = DB::table('invoices')
                    ->where('agency', (string)$agency)
                    ->whereNotNull('invoice_number')
                    ->orderBy('invoice_number', 'desc')
                    ->lockForUpdate()
                    ->value('invoice_number');

                $num = 0;
                if ($last && preg_match('/^INV-(\d+)$/', $last, $m)) {
                    $num = (int)$m[1];
                }

                $num++;
                return 'INV-' . str_pad((string)$num, 4, '0', STR_PAD_LEFT);
            });

            $invoice->invoice_number = $next;
        }

        // 5) Guardar policy_number seleccionado
        $invoice->policy_number = (string)$policyNumber;

        // 6) Guardar el JSON de tabla
        $invoice->inv_prices = json_encode($payload, JSON_UNESCAPED_UNICODE);
        $invoice->updated_at = now()->format('Y-m-d H:i:s');
        $invoice->save();

        return response()->json([
            'ok' => true,
            'invoice_id' => $invoice->id,
            'invoice_number' => $invoice->invoice_number,
            'policy_number' => $invoice->policy_number,
        ]);
    }


    public function destroy($invoiceId)
    {
        $authUser = Auth::guard('web')->user() ?? Auth::guard('sub')->user();
        if (!$authUser) return redirect()->route('login');

        $agency = $authUser->agency;

        Invoices::where('id', (string)$invoiceId)
            ->where('agency', (string)$agency)
            ->delete();

        return back()->with('success', 'Invoice deleted');
    }
}
