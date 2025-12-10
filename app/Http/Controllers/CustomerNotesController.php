<?php

namespace App\Http\Controllers;

use App\Models\CustomerNote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CustomerNotesController extends Controller
{
    // Listar notas por cliente
    public function index($customerId)
    {
        $notes = CustomerNote::where('customer_id', $customerId)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($notes);
    }

    // Guardar una nueva nota
    public function store(Request $request, $customerId)
    {
        $user = Auth::guard('web')->user() ?? Auth::guard('sub')->user();

        $note = CustomerNote::create([
            'customer_id' => $customerId,
            'policy'      => $request->policy,
            'subject'     => $request->subject,
            'note'        => $request->note,
            'created_by'  => $user->name ?? $user->username
        ]);

        return response()->json([
            'success' => true,
            'note' => $note
        ]);
    }

    // Eliminar nota
    public function destroy($id)
    {
        CustomerNote::findOrFail($id)->delete();

        return response()->json(['success' => true]);
    }
}
