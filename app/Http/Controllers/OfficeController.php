<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Models\SubUser;


class OfficeController extends Controller
{
    public function index()
    {
        $authUser = Auth::guard('web')->user() ?? Auth::guard('sub')->user();
        if (!$authUser) return redirect()->route('login');

        $agency = $authUser->agency;

        $users = User::where('agency', $agency)
            ->select('id', 'username', 'name', 'email')
            ->get()
            ->map(function ($u) {
                $u->tipo = 'Administrador';
                return $u;
            });

        $subs = SubUser::where('agency', $agency)
            ->select('id', 'username', 'name', 'email')
            ->get()
            ->map(function ($s) {
                $s->tipo = 'Usuario';
                return $s;
            });

        $members = $users->concat($subs);

        return view('office', compact('members', 'agency'));
    }

    /**
     * Eliminar un subuser (Route Model Binding).
     */
    public function destroy(Request $request, $id)
    {
        $authUser = Auth::guard('web')->user() ?? Auth::guard('sub')->user();

        if (!$authUser) {
            return redirect()->route('login');
        }

        $agency = $authUser->agency;

        // ✅ Buscar manualmente el sub-user usando el id
        $subuser = \App\Models\SubUser::where('agency', $agency)
            ->where('id', $id)
            ->first();

        if (!$subuser) {
            return back()->withErrors(['error' => 'Sub-user no encontrado o no pertenece a tu agencia.']);
        }

        try {
            $subuser->delete();

            Log::info('Sub-user eliminado correctamente', [
                'sub_id' => $id,
                'agency' => $agency,
            ]);

            return back()->with('success', 'Sub-user eliminado correctamente.');
        } catch (\Throwable $e) {
            Log::error('Error al eliminar subuser: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Error al eliminar el sub-user.']);
        }
    }
}
