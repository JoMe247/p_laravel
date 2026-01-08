<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Customer;
use App\Models\Reminder;

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

        // 🔹 Obtener los últimos 50 customers (SIN tocar tu lógica)
        $customers = Customer::orderBy('ID', 'desc')
            ->take(50)
            ->get();

        // 🔹 OBTENER REMINDERS SEGÚN SESIÓN
        $webUser = Auth::guard('web')->user();
        $subUser = Auth::guard('sub')->user();

        $reminders = collect();

        if ($webUser) {
            $reminders = Reminder::where('remind_to_type', 'user')
                ->where('remind_to_id', $webUser->id)
                ->orderBy('remind_at', 'asc')
                ->get();
        }

        if ($subUser) {
            $reminders = Reminder::where('remind_to_type', 'sub')
                ->where('remind_to_id', $subUser->id)
                ->orderBy('remind_at', 'asc')
                ->get();
        }

        $remindersCount = $reminders->count();


        // 🔹 AHORA SÍ, TODO EXISTE
        return view('dashboard', [
            'username'  => $user->name ?? $user->username,
            'customers' => $customers,
            'reminders' => $reminders,
            'remindersCount'  => $remindersCount,
        ]);
    }
}
