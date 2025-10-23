<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Customer;

class CustomersController extends Controller
{
    // Listado simple
    public function index()
    {
        $customers = Customer::orderBy('ID', 'desc')->get();
        return view('customers', compact('customers'));
    }

    // store: guarda los 4 campos rápidos (recibe JSON o form)
    public function store(Request $request)
    {
        $data = $request->only(['Name', 'Address', 'Phone', 'DOB']);

        $validated = $request->validate([
            'Name' => 'required|string|max:120',
            'Address' => 'nullable|string|max:240',
            'Phone' => 'nullable|string|max:20',
            'DOB' => 'nullable|date',
        ]);

        $customer = Customer::create($validated);

        return response()->json(['id' => $customer->ID], 201);
    }

    // muestra profile con los datos completos
    public function profile($id)
    {
        $customer = Customer::findOrFail($id);
        return view('profile', compact('customer'));
    }

    // guarda todos los campos desde profile form (POST)
    public function update(Request $request, $id)
    {
        $customer = Customer::findOrFail($id);

        // Validaciones básicas — ajusta según necesites
        $validated = $request->validate([
            'Name' => 'nullable|string|max:120',
            'Phone' => 'nullable|string|max:20',
            'Email1' => 'nullable|email|max:120',
            'Email2' => 'nullable|email|max:120',
            'Address' => 'nullable|string|max:240',
            'City' => 'nullable|string|max:30',
            'State' => 'nullable|string|max:30',
            'ZIP_Code' => 'nullable|string|max:10',
            'Drivers_License' => 'nullable|string|max:60',
            'DL_State' => 'nullable|string|max:30',
            'DOB' => 'nullable|date',
            'Source' => 'nullable|string|max:30',
            'Office' => 'nullable|string|max:40',
            'Marital' => 'nullable|string|max:30',
            'Gender' => 'nullable|string|max:30',
            'CID' => 'nullable|string|max:60',
            'Added' => 'nullable|string|max:12',
            'Agent_of_Record' => 'nullable|string|max:30',
            'Alert' => 'nullable|string|max:300',
            'Picture' => 'nullable|string|max:100',
            'Agency' => 'nullable|string|max:30',
        ]);

        $customer->fill($validated);
        $customer->save();

        return redirect()->route('customers.index')->with('success', 'Customer updated.');
    }
    public function deleteMultiple(Request $request)
    {
        $ids = $request->input('ids', []);
        if (empty($ids)) {
            return response()->json(['success' => false, 'message' => 'No se enviaron IDs.']);
        }

        \App\Models\Customer::whereIn('ID', $ids)->delete();
        return response()->json(['success' => true]);
    }
}
