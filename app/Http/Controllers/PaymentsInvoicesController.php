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

        $rows = Invoices::where('agency', (string)$agency)
            ->where('customer_id', (string)$customerId)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('invoices', compact(
            'customerId',
            'agency',
            'agencyInfo',
            'customer',
            'policiesCount',
            'rows'
        ));
    }


    public function storeRow(Request $request, $customerId)
    {
        $agency = session('agency');

        $data = $request->validate([
            'col_1'   => 'nullable|string|max:255',
            'col_2'   => 'nullable|string|max:255',
            'amount'  => 'nullable|string|max:50',
        ]);

        $id  = (string) \Illuminate\Support\Str::uuid();
        $now = now()->format('Y-m-d H:i:s');

        Invoices::create([
            'id'         => $id,
            'agency'     => (string)$agency,
            'customer_id' => (string)$customerId,
            'col_1'      => $data['col_1'] ?? '',
            'col_2'      => $data['col_2'] ?? '',
            'amount'     => $data['amount'] ?? '',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        return response()->json(['ok' => true, 'id' => $id]);
    }
}
