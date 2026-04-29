<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Customer;
use App\Models\CustomerNote;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;



class CustomersController extends Controller
{
    // Listado simple
    public function index()
    {
        $user = Auth::guard('web')->user() ?? Auth::guard('sub')->user();

        // En caso de no estar autenticado, redirige al login
        if (!$user) {
            return redirect()->route('login');
        }

        $customers = Customer::orderBy('ID', 'desc')->paginate(50);

        // Obtener los IDs de los customers que están en esta página
        $customerIds = $customers->pluck('ID')->toArray();

        // Contar cuántas policies tiene cada customer
        $policyCounts = DB::table('policies')
            ->select('customer_id', DB::raw('COUNT(*) as total'))
            ->whereIn('customer_id', $customerIds)
            ->groupBy('customer_id')
            ->pluck('total', 'customer_id')
            ->toArray();

        return view('customers', compact('customers', 'policyCounts'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'Name'    => 'required|string|max:120',
            'Address' => 'nullable|string|max:240',
            'Phone'   => 'nullable|string|max:20',
            'DOB'     => 'nullable|date',
        ]);

        // Detectar quién está logueado (user o sub user)
        $user = Auth::guard('web')->user() ?? Auth::guard('sub')->user();

        // name del agente (user/sub user)
        $agentName = $user->name ??  null;

        // agency_code del agente (ajusta si tu columna se llama distinto)
        $agencyCode = $user->agency ?? null;

        $validated['Added'] = now()->format('Y-m-d');

        // Guardar en columnas del customer
        // OJO: si tu DB tiene límites de varchar más chicos, ajusta max o usa Str::limit.
        $validated['Agent_of_Record'] = $agentName ? Str::limit($agentName, 30, '') : null;
        $validated['Agency']          = $agencyCode ? Str::limit($agencyCode, 30, '') : null;

        $customer = Customer::create($validated);

        return response()->json(['id' => $customer->ID], 201);
    }


    // muestra profile con los datos completos
    public function profile($id)
    {
        $user = Auth::guard('web')->user() ?? Auth::guard('sub')->user();

        if (!$user) {
            return redirect()->route('login');
        }

        $customer = Customer::findOrFail($id);

        $customerViews = collect($customer->views ?? [])
            ->sortByDesc(function ($item) {
                return strtotime($item['created_at'] ?? '') ?: 0;
            })
            ->values()
            ->all();

        $notes = CustomerNote::where('customer_id', $id)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('profile', compact('customer', 'notes', 'customerViews'));
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
        $newName = uniqid('cust_') . '.' . $file->getClientOriginalExtension();
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

    public function logProfileView($id)
    {
        $user = Auth::guard('web')->user() ?? Auth::guard('sub')->user();

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $customer = Customer::findOrFail($id);

        $viewerName = $user->name ?? $user->username ?? $user->email ?? 'System';
        $now = now();

        $views = is_array($customer->views) ? $customer->views : [];

        $entry = [
            'type'       => 'CUSTOMER VIEW',
            'by'         => $viewerName,
            'created_at' => $now->format('Y-m-d H:i:s'),
            'message'    => 'Customer View By ' . $viewerName . ' on ' . $now->format('m/d/y h:i:s A'),
        ];

        $views[] = $entry;

        $customer->views = array_values($views);
        $customer->save();

        return response()->json([
            'success' => true,
            'entry' => [
                'type'       => $entry['type'],
                'by'         => $entry['by'],
                'created_at' => $entry['created_at'],
                'full_date'  => $now->format('l, F j, Y h:i:s A'),
                'short_date' => $now->format('m/d/y h:i:s A'),
                'message'    => $entry['message'],
            ]
        ]);
    }
}
