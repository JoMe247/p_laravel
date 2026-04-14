<?php

namespace App\Http\Controllers;

use App\Models\Policy;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PoliciesController extends Controller
{
    public function index($customer_id)
    {
        $customer = Customer::findOrFail($customer_id);
        $policies = Policy::where('customer_id', $customer_id)
            ->orderBy('id', 'desc')
            ->get();

        $policyLog = DB::table('policies')
            ->where('customer_id', $customer->ID)
            ->orderByDesc('id')
            ->limit(10)
            ->get([
                'pol_number',
                'pol_eff_date',
                'pol_expiration',
                'pol_due_day',
                'pol_carrier',
                'pol_url',
            ]);

        return view('policies', compact('customer', 'policies', 'policyLog'));
    }

    public function store(Request $request, $customer_id)
    {
        $data = $request->validate([
            'pol_carrier'      => 'nullable|string',
            'pol_number'       => 'nullable|string',
            'pol_url'          => 'nullable|string',
            'pol_expiration'   => 'nullable|date',
            'pol_eff_date'     => 'nullable|date',
            'pol_added_date'   => 'nullable|date',
            'pol_due_day'      => 'nullable|string',
            'pol_status'       => 'nullable|string',
            'pol_agent_record' => 'nullable|string',
            'vehicules'        => 'nullable|string',
        ]);

        $data['customer_id'] = $customer_id;

        // Default status solo para nueva policy
        $data['pol_status'] = !empty($data['pol_status']) ? $data['pol_status'] : 'Active';

        if (!empty($data['vehicules'])) {
            $decoded = json_decode($data['vehicules'], true);
            $data['vehicules'] = is_array($decoded) ? $decoded : [];
        } else {
            $data['vehicules'] = [];
        }

        Policy::create($data);

        return response()->json(['success' => true]);
    }

    public function destroy($id)
    {
        Policy::where('id', $id)->delete();

        return response()->json(['success' => true]);
    }

    public function show($id)
    {
        $p = Policy::findOrFail($id);

        return response()->json([
            'success' => true,
            'policy'  => $p,
        ]);
    }

    public function update(Request $request, $id)
    {
        $policy = Policy::findOrFail($id);

        $data = $request->validate([
            'pol_carrier'      => 'nullable|string',
            'pol_number'       => 'nullable|string',
            'pol_url'          => 'nullable|string',
            'pol_expiration'   => 'nullable|date',
            'pol_eff_date'     => 'nullable|date',
            'pol_added_date'   => 'nullable|date',
            'pol_due_day'      => 'nullable|string',
            'pol_status'       => 'nullable|string',
            'pol_agent_record' => 'nullable|string',
            'vehicules'        => 'nullable|string',
        ]);

        if (array_key_exists('pol_status', $data) && $data['pol_status'] === '') {
            $data['pol_status'] = 'Active';
        }

        if (!empty($data['vehicules'])) {
            $decoded = json_decode($data['vehicules'], true);
            $data['vehicules'] = is_array($decoded) ? $decoded : [];
        } else {
            $data['vehicules'] = [];
        }

        $policy->update($data);

        return response()->json(['success' => true]);
    }
}
