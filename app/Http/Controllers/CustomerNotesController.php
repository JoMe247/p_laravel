<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CustomerNote;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use App\Models\Policy;


class CustomerNotesController extends Controller
{
    /**
     * Obtener notas de un cliente
     */
    public function index($customerId)
    {
        // User o SubUser autenticado
        $user = Auth::guard('web')->user() ?? Auth::guard('sub')->user();

        $notes = CustomerNote::where('customer_id', $customerId)
            ->where('agency', $user->agency) // 🔐 FILTRO POR AGENCY
            ->orderBy('id', 'desc')
            ->get([
                'id',
                'policy',
                'subject',
                'note',
                'created_by',
                'created_at'
            ]);

        return response()->json($notes);
    }


    /**
     * Guardar una nueva nota
     */
    public function store(Request $request, $customerId)
    {
        $user = Auth::guard('web')->user() ?? Auth::guard('sub')->user();

        // DEBUG — para saber qué está recibiendo Laravel
        Log::info("USER DEBUG", [
            'id' => $user->id ?? null,
            'name' => $user->name ?? null,
            'username' => $user->username ?? null,
            'type' => get_class($user)
        ]);

        $request->validate([
            'policy'  => [
                'nullable',
                'string',
                'max:120',
                Rule::exists('policies', 'pol_number')->where(function ($q) use ($customerId, $user) {
                    $q->where('customer_id', $customerId);
                }),
            ],
            'subject' => 'required|string|max:200',
            'note'    => 'required|string|max:2000',
        ]);


        $creatorName = $user->name ?? $user->username;

        $note = CustomerNote::create([
            'customer_id' => $customerId,
            'agency'      => $user->agency,
            'policy'      => $request->policy,
            'subject'     => $request->subject,
            'note'        => $request->note,
            'created_by'  => $creatorName,
            'created_at'  => now()
        ]);

        return response()->json([
            'success' => true,
            'note' => $note
        ]);
    }

    /**
     * Eliminar nota
     */
    public function destroy($id)
    {
        CustomerNote::findOrFail($id)->delete();

        return response()->json([
            'success' => true
        ]);
    }

    public function policies($customerId)
    {
        $user = Auth::guard('web')->user() ?? Auth::guard('sub')->user();

        // Traer pólizas del customer (filtrando por agency si tu tabla policies tiene agency)
        $policies = Policy::where('customer_id', $customerId)
            ->orderBy('pol_number', 'asc')
            ->get(['pol_number']);

        // Regresamos solo un array simple
        return response()->json($policies->pluck('pol_number'));
    }
}
