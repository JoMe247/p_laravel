<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Models\SubUser;
use App\Models\Agency;
use Illuminate\Support\Facades\DB;


class OfficeController extends Controller
{
    public function index()
    {
        $authUser = Auth::guard('web')->user() ?? Auth::guard('sub')->user();
        if (!$authUser) return redirect()->route('login');

        $agency = $authUser->agency;

        // Buscar información de la agencia por agency_code
        $agencyData = Agency::where('agency_code', $agency)->first() ?? new Agency();

        $twilioNumber = $authUser->twilio_number ?? '';

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

        // ================================
        //   PLAN - LIMITES DE USUARIOS
        // ================================
        // 1. Obtener el tipo de plan
        $plan = DB::connection('doc_config')
            ->table('limits')
            ->where('account_type', $agencyData->account_type)
            ->first();

        // Plan de fallback
        if (!$plan) {
            $plan = DB::connection('doc_config')
                ->table('limits')
                ->where('account_type', 'P1')
                ->first();
        }

        // 2. Calcular total de usuarios actuales
        $totalUsers =
            User::where('agency', $agency)->count() +
            SubUser::where('agency', $agency)->count();

        $userLimit = (int) $plan->user_limit;

        // 3. Verificar si alcanzó el límite
        $isUserLimitReached = $totalUsers >= $userLimit;

        return view('office', compact(
            'members',
            'agency',
            'agencyData',
            'twilioNumber',
            'plan',
            'totalUsers',
            'userLimit',
            'isUserLimitReached'
        ));

        return view('office', compact('members', 'agency', 'agencyData', 'twilioNumber'));
    }

    /**
     * Guardar / actualizar datos de agency usando agency_code
     */
    public function saveAgency(Request $request)
    {
        $authUser = Auth::guard('web')->user() ?? Auth::guard('sub')->user();
        if (!$authUser) return redirect()->route('login');

        $request->validate([
            'agency_code'    => 'required|string',
            'agency_name'    => 'required|string|max:100',
            'agency_email'   => 'required|email|max:100',
            'office_phone'   => 'nullable|string|max:13',
            'agency_address' => 'nullable|string|max:260',
        ]);

        // 👉 Buscar registro por agency_code o crearlo si no existe
        $agency = Agency::firstOrNew([
            'agency_code' => $request->agency_code
        ]);

        // 👉 Asignar valores
        $agency->agency_code    = $request->agency_code;
        $agency->agency_name    = $request->agency_name;
        $agency->agency_email   = $request->agency_email;
        $agency->office_phone   = $request->office_phone;
        $agency->agency_address = $request->agency_address;

        // 👉 Guardar o actualizar
        $agency->save();

        return back()->with('success', 'Datos de agencia guardados correctamente.');
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

        // Sub users NO pueden eliminar
        if (auth('sub')->check()) {
            return back()->withErrors(['error' => 'Los sub users no pueden eliminar usuarios.']);
        }

        $agency = $authUser->agency;

        // Verificar que el sub user pertenece a la misma agencia
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

    public function uploadLogo(Request $request)
    {
        $authUser = Auth::guard('web')->user() ?? Auth::guard('sub')->user();
        if (!$authUser) return redirect()->route('login');

        $request->validate([
            'agency_logo' => 'required|image|mimes:jpg,jpeg,png,webp|max:2048'
        ]);

        $agencyCode = $authUser->agency;

        $agency = Agency::where('agency_code', $agencyCode)->first();
        if (!$agency) {
            return back()->withErrors(['error' => 'Agencia no encontrada.']);
        }

        // Guardar archivo
        $file = $request->file('agency_logo');
        $name = 'agency_logo_' . $agencyCode . '.' . $file->extension();
        $path = $file->storeAs('agency_logos', $name, 'public');

        // Guardar ruta en base de datos
        $agency->agency_logo = $path;
        $agency->save();

        return back()->with('success', 'Logo actualizado correctamente.');
    }
}
