<?php

namespace App\Http\Controllers;

use App\Models\Policy;
use App\Models\Customer;
use Illuminate\Http\Request;

class PoliciesController extends Controller
{
    public function index($customer_id)
    {
        $customer = Customer::findOrFail($customer_id);
        $policies = Policy::where('customer_id', $customer_id)->orderBy('id', 'desc')->get();

        return view('policies', compact('customer', 'policies'));
    }

    public function store(Request $request, $customer_id)
    {
        $data = $request->validate([
            'pol_carrier' => 'nullable|string',
            'pol_number' => 'nullable|string',
            'pol_url' => 'nullable|string',
            'pol_expiration' => 'nullable|date',
            'pol_eff_date' => 'nullable|date',
            'pol_added_date' => 'nullable|date',
            'pol_due_day' => 'nullable|string',
            'pol_status' => 'nullable|string',
            'pol_agent_record' => 'nullable|string',
            'vehicules'        => 'nullable|string',
        ]);

        $data['customer_id'] = $customer_id;

        if (!empty($data['vehicules'])) {
            $data['vehicules'] = json_decode($data['vehicules'], true);
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
            'pol_expiration'   => 'nullable|string',
            'pol_eff_date'     => 'nullable|string',
            'pol_added_date'   => 'nullable|string',
            'pol_due_day'      => 'nullable|string',
            'pol_status'       => 'nullable|string',
            'pol_agent_record' => 'nullable|string',
            'vehicules'        => 'nullable|string', // viene como JSON
        ]);

        if (!empty($data['vehicules'])) {
            $data['vehicules'] = json_decode($data['vehicules'], true);
        }

        $policy->update($data);

        return response()->json(['success' => true]);
    }
}
