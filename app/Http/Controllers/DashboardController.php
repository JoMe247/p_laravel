<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Customer;

class DashboardController extends Controller
{
    public function show()
    {
        // Obtener usuario autenticado (funciona también con remember me)
        $user = Auth::guard('web')->user() ?? Auth::guard('sub')->user();

        // En caso de no estar autenticado, redirige al login
        if (!$user) {
            return redirect()->route('login');
        }

        // 🔹 Obtener los últimos 50 customers (sin tocar tu lógica actual)
        $customers = Customer::orderBy('ID', 'desc')
            ->take(50)
            ->get();

        // Pasamos el nombre de usuario a la vista + customers
        return view('dashboard', [
            'username' => $user->name ?? $user->username,
            'customers' => $customers,   // ⬅️ agregado
        ]);
    }
}
