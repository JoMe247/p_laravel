<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CalendarEvent;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\ScheduleAssignment;
use Illuminate\Support\Facades\Log;


class CalendarController extends Controller
{
    public function index()
    {
        $today = Carbon::today()->toDateString();

        // ===============================
        // 🔐 Detectar guard e ID real
        // ===============================
        $guard  = session('auth_guard'); // 'web' (user) o 'sub'
        $authId = null;

        if ($guard) {
            foreach (array_keys(session()->all()) as $key) {
                if (str_starts_with($key, 'login_' . $guard . '_')) {
                    $authId = session($key);
                    break;
                }
            }
        }

        // Si no hay sesión válida, no mostramos turno
        if (!$guard || !$authId) {
            $todayShift = null;
            return view('calendar', compact('todayShift'));
        }

        // ===============================
        // 🎯 Mapear a schedule_assignments
        // ===============================
        $targetType = ($guard === 'sub') ? 'sub' : 'user';
        $targetId   = (int) $authId;

        // Agency (si existe en sesión)
        $agency = session('agency') ?? null;

        // ===============================
        // 📅 Obtener turno de HOY
        // ===============================
        $todayShift = ScheduleAssignment::with('shift')
            ->whereDate('shift_date', $today)
            ->where('target_type', $targetType)
            ->where('target_id', $targetId)
            ->when($agency, fn($q) => $q->where('agency', $agency))
            ->orderByDesc('id')
            ->first();

        return view('calendar', compact('todayShift'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title'               => 'required|string|max:255',
            'description'         => 'nullable|string',
            'start_date'          => 'required|date',
            'end_date'            => 'nullable|date|after_or_equal:start_date',
            'notification_value'  => 'nullable|integer|min:0',
            'notification_unit'   => 'nullable|in:minutes,hours',
            'color'               => 'nullable|string|max:20',
            'is_public'           => 'nullable|boolean',
        ]);

        CalendarEvent::create([
            'user_id' => Auth::id(),
            ...$data
        ]);

        return response()->json(['status' => 'success']);
    }

    public function load()
    {
        $events = CalendarEvent::where('user_id', Auth::id())->get();

        $formatted = $events->map(function ($e) {
            return [
                'id'     => $e->id,
                'title'  => $e->title,
                'start'  => $e->start_date,
                'end'    => $e->end_date,
                'color'  => $e->color,
                'extendedProps' => [
                    'description' => $e->description,
                    'is_public'   => $e->is_public,
                    'notification_value' => $e->notification_value,
                    'notification_unit'  => $e->notification_unit,
                ]
            ];
        });

        return response()->json($formatted);
    }


    public function update(Request $request)
    {
        $event = CalendarEvent::findOrFail($request->id);

        $event->update([
            'title' => $request->title,
            'description' => $request->description,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'notification_value' => $request->notification_value,
            'notification_unit' => $request->notification_unit,
            'color' => $request->color,
            'is_public' => $request->is_public ? 1 : 0
        ]);

        return response()->json(['status' => 'updated']);
    }


    public function delete($id)
    {
        CalendarEvent::where('id', $id)->delete();
        return response()->json(['status' => 'deleted']);
    }
}
