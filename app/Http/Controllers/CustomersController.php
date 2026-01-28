<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Customer;
use App\Models\CustomerNote;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;


class CustomersController extends Controller
{
    // Listado simple
    public function index()
    {
        $customers = Customer::orderBy('ID', 'desc')->get();

        // ids de customers
        $customerIds = $customers->pluck('ID')->filter()->values()->all();

        // Consulta independiente (NO depende de relaciones)
        $policyCounts = $this->getPolicyCountsByCustomerId($customerIds);

        return view('customers', compact('customers', 'policyCounts'));
    }

    private function getPolicyCountsByCustomerId(array $customerIds): array
    {
        if (empty($customerIds)) return [];

        // OJO: cambia 'policies' si tu tabla se llama diferente
        return DB::table('policies')
            ->whereIn('customer_id', $customerIds) // OJO: cambia customer_id si tu campo se llama diferente
            ->selectRaw('customer_id, COUNT(*) as total')
            ->groupBy('customer_id')
            ->pluck('total', 'customer_id')
            ->toArray();
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

        $validated['Added'] = now()->format('Y-m-d');

        $customer = Customer::create($validated);

        return response()->json(['id' => $customer->ID], 201);
    }

    // muestra profile con los datos completos
    public function profile($id)
    {
        $customer = Customer::findOrFail($id);

        // Cargar notas
        $notes = CustomerNote::where('customer_id', $id)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('profile', compact('customer', 'notes'));
    }

    // guarda todos los campos desde profile form (POST)
    public function update(Request $request, $id)
    {
        $customer = Customer::findOrFail($id);

        // Validaciones básicas — ajusta según necesites
        $validated = $request->validate([
            'Name' => 'nullable|string|max:120',
            'Phone' => 'nullable|string|max:12',
            'Phone2' => 'nullable|string|max:12',
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
            //'Added' => 'nullable|date',
            'Agent_of_Record' => 'nullable|string|max:30',
            //'Picture' => 'nullable|string|max:255',
            'Alert' => 'nullable|string|max:300',
            'Agency' => 'nullable|string|max:30',
        ]);

        $customer->fill($validated);
        $customer->save();

        return redirect('profile/'.$id)->with('success', 'Customer updated.');
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

    public function uploadPhoto(Request $request, $id)
    {
        $request->validate([
            'Picture' => 'nullable|string|max:255'
        ]);

        $customer = Customer::findOrFail($id);

        // Eliminar foto anterior
        if ($customer->Picture && file_exists(public_path($customer->Picture))) {
            @unlink(public_path($customer->Picture));
        }

        // Guardar nueva foto
        $file = $request->file('photo');
        $newName = $id . '.' . $file->getClientOriginalExtension();
        $file->move(public_path('uploads/customers'), $newName);

        $path = 'uploads/customers/' . $newName;

        // Guardar ruta en DB
        $customer->Picture = $path;
        $customer->save();

        return response()->json([
            'success' => true,
            'path' => asset($path)
        ]);
    }

    public function saveAlert(Request $request, $id)
    {
        $request->validate([
            'Alert' => 'required|string|max:300',
        ]);

        $customer = Customer::findOrFail($id);
        $customer->Alert = $request->Alert;
        $customer->save();

        return response()->json([
            'success' => true,
            'alert' => $customer->Alert
        ]);
    }

    public function removeAlert($id)
    {
        $customer = Customer::findOrFail($id);
        $customer->Alert = null;
        $customer->save();

        return response()->json(['success' => true]);
    }

    public function saveNote(Request $request, $id)
    {
        $request->validate([
            'policy'  => 'nullable|string|max:120',
            'subject' => 'required|string|max:200',
            'note'    => 'required|string|max:2000',
        ]);

        // Detectar quién está logueado (user o sub user)
        $user =
            Auth::guard('web')->user() ??
            Auth::guard('sub')->user();

        $note = CustomerNote::create([
            'customer_id' => $id,
            'policy'      => $request->policy,
            'subject'     => $request->subject,
            'note'        => $request->note,
            'created_by'  => $user->name ?? $user->username,
            'created_at'  => now()
        ]);

        return response()->json([
            'success' => true,
            'note' => $note
        ]);
    }

    public function deleteNote($noteId)
    {
        CustomerNote::where('id', $noteId)->delete();

        return response()->json(['success' => true]);
    }
}
