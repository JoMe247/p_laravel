<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CustomerNote;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CustomerNotesController extends Controller
{
    /**
     * Obtener notas de un cliente
     */
    public function index($customerId)
    {
        $notes = CustomerNote::where('customer_id', $customerId)
            ->orderBy('id', 'desc')
            ->get()
            ->map(function ($note) {
                return [
                    'id'          => $note->id,
                    'policy'      => $note->policy,
                    'subject'     => $note->subject,
                    'note'        => $note->note,
                    'created_by'  => $note->created_by,
                    'created_at'  => $note->created_at ? $note->created_at : null
                ];
            });

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

    $creatorName = $user->name ?? $user->username;

    $note = CustomerNote::create([
        'customer_id' => $customerId,
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
}
