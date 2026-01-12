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

        return view('payments', [
            'customerId' => $customerId,
            'agency' => $agency,
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

        $policiesCount = DB::table('policies')
            ->where('customer_id', $customerId)
            ->count();

        $policyNumbers = DB::table('policies')
            ->where('customer_id', $customerId)
            ->orderBy('id', 'asc')
            ->pluck('pol_number')
            ->filter()
            ->values();


        $rows = Invoices::where('agency', (string)$agency)
            ->where('customer_id', (string)$customerId)
            ->orderBy('created_at', 'desc')
            ->get();

        $invoiceMeta = Invoices::where('agency', (string)$agency)
            ->where('customer_id', (string)$customerId)
            ->orderBy('created_at', 'desc')
            ->first();

        $creationDate = $invoiceMeta->creation_date ?? '';
        $paymentDate  = $invoiceMeta->payment_date ?? '';


        return view('invoices', compact(
            'customerId',
            'agency',
            'agencyInfo',
            'customer',
            'policiesCount',
            'rows',
            'policyNumbers',
            'creationDate',
            'paymentDate'
        ));
    }


    public function storeRow(Request $request, $customerId)
    {
        $agency = session('agency');

        $data = $request->validate([
            'item'   => 'nullable|string|max:255',
            'col_2'   => 'nullable|string|max:255',
            'amount'  => 'nullable|string|max:50',
        ]);

        $id  = (string) \Illuminate\Support\Str::uuid();
        $now = now()->format('Y-m-d H:i:s');

        Invoices::create([
            'id'         => $id,
            'agency'     => (string)$agency,
            'customer_id' => (string)$customerId,
            'item'      => $data['item'] ?? '',
            'col_2'      => $data['col_2'] ?? '',
            'amount'     => $data['amount'] ?? '',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        return response()->json(['ok' => true, 'id' => $id]);
    }

    public function saveDates(Request $request, $customerId)
    {
        $authUser = \Illuminate\Support\Facades\Auth::guard('web')->user() ?? \Illuminate\Support\Facades\Auth::guard('sub')->user();
        if (!$authUser) return response()->json(['ok' => false], 401);

        $agency = $authUser->agency;

        $data = $request->validate([
            'creation_date' => 'nullable|string|max:30',
            'payment_date'  => 'nullable|string|max:30',
        ]);

        Invoices::where('agency', (string)$agency)
            ->where('customer_id', (string)$customerId)
            ->update([
                'creation_date' => $data['creation_date'] ?? '',
                'payment_date'  => $data['payment_date'] ?? '',
                'updated_at'    => now()->format('Y-m-d H:i:s'),
            ]);

        return response()->json(['ok' => true]);
    }
}
