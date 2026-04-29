<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Models\SubUser;
use App\Models\Agency;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;



class OfficeController extends Controller
{
    public function index()
    {
        $authUser = Auth::guard('web')->user() ?? Auth::guard('sub')->user();

        if (!$authUser) {
            return redirect()->route('login');
        }

        $agency = $authUser->agency;

        // Buscar información de la agencia por agency_code
        $agencyData = Agency::where('agency_code', $agency)->first() ?? new Agency();

        $twilioNumber = $authUser->twilio_number ?? '';

        $onlineLimit = now()->subMinutes(5);

        $users = User::where('agency', $agency)
            ->select('id', 'username', 'name', 'email', 'last_seen_at')
            ->get()
            ->map(function ($u) use ($onlineLimit) {
                $lastSeen = $u->last_seen_at ? Carbon::parse($u->last_seen_at) : null;

                $u->tipo = 'Administrador';
                $u->is_online = $lastSeen && $lastSeen->greaterThanOrEqualTo($onlineLimit);
                $u->last_seen_text = $lastSeen
                    ? 'Last seen: ' . $lastSeen->format('m/d/Y h:i A')
                    : 'Last seen: never';

                return $u;
            });

        $subs = SubUser::where('agency', $agency)
            ->select('id', 'username', 'name', 'email', 'last_seen_at')
            ->get()
            ->map(function ($s) use ($onlineLimit) {
                $lastSeen = $s->last_seen_at ? Carbon::parse($s->last_seen_at) : null;

                $s->tipo = 'Usuario';
                $s->is_online = $lastSeen && $lastSeen->greaterThanOrEqualTo($onlineLimit);
                $s->last_seen_text = $lastSeen
                    ? 'Last seen: ' . $lastSeen->format('m/d/Y h:i A')
                    : 'Last seen: never';

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

        if (!$authUser) {
            return redirect()->route('login');
        }

        $request->validate([
            'agency_logo'  => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
            'cropped_logo' => 'required|string',
        ]);

        $agencyCode = $authUser->agency;

        $agency = Agency::where('agency_code', $agencyCode)->first();

        if (!$agency) {
            return back()->withErrors(['error' => 'Agencia no encontrada.']);
        }

        $croppedLogo = $request->input('cropped_logo');

        if (!preg_match('/^data:image\/(png|jpg|jpeg|webp);base64,/', $croppedLogo, $matches)) {
            return back()->withErrors(['error' => 'Formato de imagen inválido.']);
        }

        $extension = $matches[1] === 'jpeg' ? 'jpg' : $matches[1];

        $imageData = substr($croppedLogo, strpos($croppedLogo, ',') + 1);
        $imageData = base64_decode($imageData, true);

        if ($imageData === false) {
            return back()->withErrors(['error' => 'No se pudo procesar la imagen.']);
        }

        $safeAgencyCode = preg_replace('/[^A-Za-z0-9_-]/', '_', $agencyCode);

        $name = 'agency_logo_' . $safeAgencyCode . '.' . $extension;
        $path = 'agency_logos/' . $name;

        if ($agency->agency_logo && $agency->agency_logo !== $path && Storage::disk('public')->exists($agency->agency_logo)) {
            Storage::disk('public')->delete($agency->agency_logo);
        }

        Storage::disk('public')->put($path, $imageData);

        $agency->agency_logo = $path;
        $agency->save();

        return back()->with('success', 'Logo actualizado correctamente.');
    }

    public function deleteLogo(Request $request)
    {
        $authUser = Auth::guard('web')->user() ?? Auth::guard('sub')->user();

        if (!$authUser) {
            return redirect()->route('login');
        }

        $agencyCode = $authUser->agency;

        $agency = Agency::where('agency_code', $agencyCode)->first();

        if (!$agency) {
            return back()->withErrors(['error' => 'Agencia no encontrada.']);
        }

        if ($agency->agency_logo && Storage::disk('public')->exists($agency->agency_logo)) {
            Storage::disk('public')->delete($agency->agency_logo);
        }

        $agency->agency_logo = null;
        $agency->save();

        return back()->with('success', 'Logo eliminado correctamente.');
    }

    public function update(Request $request, $id)
    {
        $authUser = Auth::guard('web')->user() ?? Auth::guard('sub')->user();
        if (!$authUser) return redirect()->route('login');

        // Sub users NO pueden editar
        if (auth('sub')->check()) {
            return back()->withErrors(['error' => 'Los sub users no pueden editar usuarios.']);
        }

        $agency = $authUser->agency;

        $subuser = SubUser::where('agency', $agency)->where('id', $id)->first();
        if (!$subuser) {
            return back()->withErrors(['error' => 'Sub-user no encontrado o no pertenece a tu agencia.']);
        }

        $request->validate([
            'name'     => 'required|string|max:100',
            'email'    => 'required|email|max:100|unique:sub_users,email,' . $subuser->id,
            'password' => 'nullable|string|min:8', // igual que tu store()
        ]);

        $subuser->name  = $request->name;
        $subuser->email = $request->email;

        // ✅ CLAVE: tu password real es password_hash
        if ($request->filled('password')) {
            $subuser->password_hash = Hash::make($request->password);
        }

        $subuser->save();

        return back()->with('success', 'Sub-user actualizado correctamente.');
    }
}
