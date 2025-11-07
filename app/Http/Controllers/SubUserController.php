<?php

namespace App\Http\Controllers;

use App\Models\SubUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class SubUserController extends Controller
{
    public function create()
    {
        // Si quieres mostrar la agencia en la vista:
        $creator = auth('web')->user(); // Dueño autenticado
        $agency  = $creator->agency ?? null;
        $twilio  = $creator->twilio_from ?? null;

        return view('office', compact('agency', 'twilio'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'username' => 'required|string|max:50|unique:sub_users,username',
            'name'     => 'required|string|max:100',
            'email'    => 'required|email|max:100|unique:sub_users,email',
            'password' => 'required|string|min:8',
        ]);

        $creator = auth('web')->user();

        // Heredamos agencia y número de Twilio del usuario creador
        $agency = $creator->agency ?? null;
        $twilio = $creator->twilio_number ?? null; // 👈 coincide con tu tabla

        $sub = \App\Models\SubUser::create([
            'username'      => $request->username,
            'name'          => $request->name,
            'email'         => $request->email,
            'password_hash' => \Illuminate\Support\Facades\Hash::make($request->password),
            'agency'        => $agency,
            'twilio_number' => $twilio, // 👈 campo correcto
        ]);

        \Illuminate\Support\Facades\Log::debug('SubUser creado', [
            'sub_id' => $sub->id,
            'agency' => $agency,
        ]);

        return redirect()
            ->route('office.create')
            ->with('success', 'Sub-usuario creado correctamente.');
    }
}
