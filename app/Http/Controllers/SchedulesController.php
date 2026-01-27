<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\User;
use App\Models\SubUser;
use App\Models\ScheduleShift;
use App\Models\ScheduleAssignment;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class SchedulesController extends Controller
{
    private function actor()
    {
        return Auth::guard('web')->user() ?? Auth::guard('sub')->user();
    }

    private function isOwnerUser(): bool
    {
        return Auth::guard('web')->check();
    }

    private function agencyOf($actor): ?string
    {
        return $actor->agency ?? null;
    }

    public function index(Request $request)
    {
        $actor = $this->actor();
        if (!$actor) return redirect()->route('login');

        $agency = $this->agencyOf($actor);
        $isOwner = $this->isOwnerUser();

        // Fecha base (si no viene, hoy)
        $base = $request->query('date')
            ? Carbon::parse($request->query('date'))
            : now();

        $start = $base->copy()->startOfWeek(Carbon::MONDAY);
        $end   = $base->copy()->endOfWeek(Carbon::SUNDAY);

        return view('schedules', [
            'isOwner' => $isOwner,
            'agency' => $agency,
            'weekStart' => $start->toDateString(),
            'weekEnd' => $end->toDateString(),
        ]);
    }

    public function weekData(Request $request)
    {
        $actor = $this->actor();
        if (!$actor) return response()->json(['error' => 'unauthorized'], 401);

        $agency = $this->agencyOf($actor);

        $base = $request->query('date')
            ? Carbon::parse($request->query('date'))
            : now();

        $start = $base->copy()->startOfWeek(Carbon::MONDAY);
        $end   = $base->copy()->endOfWeek(Carbon::SUNDAY);

        $users = User::where('agency', $agency)->select('id', 'name', 'username')->get();
        $subs  = SubUser::where('agency', $agency)->select('id', 'name', 'username')->get();

        $people = [];

        foreach ($users as $u) {
            $people[] = [
                'type' => 'user',
                'id' => $u->id,
                'name' => $u->name ?? $u->username,
            ];
        }
        foreach ($subs as $s) {
            $people[] = [
                'type' => 'sub',
                'id' => $s->id,
                'name' => $s->name ?? $s->username,
            ];
        }

        // assignments de esa semana
        $assignments = ScheduleAssignment::with('shift')
            ->where('agency', $agency)
            ->whereBetween('shift_date', [$start->toDateString(), $end->toDateString()])
            ->get();

        return response()->json([
            'week' => [
                'start' => $start->toDateString(),
                'end' => $end->toDateString(),
                'title' => $start->format('M d') . ' - ' . $end->format('M d, Y')
            ],
            'people' => $people,
            'assignments' => $assignments->map(function ($a) {
                return [
                    'date' => $a->shift_date,
                    'target_type' => $a->target_type,
                    'target_id' => $a->target_id,
                    'shift' => [
                        'id' => $a->shift->id,
                        'color' => $a->shift->color,
                        'is_time_off' => (bool)$a->shift->is_time_off,
                        'time_off_type' => $a->shift->time_off_type,
                        'time_text' => $a->shift->time_text,
                    ]
                ];
            }),
            'canEdit' => $this->isOwnerUser(),
        ]);
    }

    public function getShifts(Request $request)
    {
        $actor = $this->actor();
        if (!$actor) return response()->json(['error' => 'unauthorized'], 401);

        $agency = $this->agencyOf($actor);

        $shifts = ScheduleShift::where('agency', $agency)
            ->orderBy('id', 'desc')
            ->get();

        return response()->json([
            'canEdit' => $this->isOwnerUser(),
            'shifts' => $shifts
        ]);
    }

    public function storeShift(Request $request)
    {
        if (!$this->isOwnerUser()) return response()->json(['error' => 'forbidden'], 403);

        $actor = $this->actor();
        $agency = $this->agencyOf($actor);

        $data = $request->validate([
            'assign_type' => 'nullable|string|max:10',
            'assign_id' => 'nullable|integer',
            'color' => 'nullable|string|max:30',
            'is_time_off' => 'required|boolean',
            'time_off_type' => 'nullable|string|max:50',
            'time_text' => 'nullable|string|max:40',
        ]);

        if ($data['is_time_off']) {
            if (empty($data['time_off_type'])) {
                return response()->json(['error' => 'time_off_type_required'], 422);
            }
            $data['time_text'] = null;
        } else {
            if (empty($data['time_text'])) {
                return response()->json(['error' => 'time_required'], 422);
            }
            $data['time_off_type'] = null;
        }

        $shift = ScheduleShift::create([
            'agency' => $agency,
            'assign_type' => $data['assign_type'] ?? 'any',
            'assign_id' => $data['assign_id'] ?? null,
            'color' => $data['color'] ?? null,
            'is_time_off' => $data['is_time_off'],
            'time_off_type' => $data['time_off_type'] ?? null,
            'time_text' => $data['time_text'] ?? null,
            'created_by_user_id' => $actor->id,
        ]);

        return response()->json(['ok' => true, 'shift' => $shift]);
    }

    public function updateShift(Request $request, $id)
    {
        if (!$this->isOwnerUser()) return response()->json(['error' => 'forbidden'], 403);

        $actor = $this->actor();
        $agency = $this->agencyOf($actor);

        $shift = ScheduleShift::where('agency', $agency)->where('id', $id)->firstOrFail();

        $data = $request->validate([
            'color' => 'nullable|string|max:30',
            'is_time_off' => 'required|boolean',
            'time_off_type' => 'nullable|in:Holiday,Personal,Sick',
            'time_text' => 'nullable|string|max:40',
        ]);

        if ($data['is_time_off']) {
            if (empty($data['time_off_type'])) return response()->json(['error' => 'time_off_type_required'], 422);
            $data['time_text'] = null;
        } else {
            if (empty($data['time_text'])) return response()->json(['error' => 'time_required'], 422);
            $data['time_off_type'] = null;
        }

        $shift->update([
            'color' => $data['color'] ?? null,
            'is_time_off' => $data['is_time_off'],
            'time_off_type' => $data['time_off_type'] ?? null,
            'time_text' => $data['time_text'] ?? null,
        ]);

        return response()->json(['ok' => true, 'shift' => $shift]);
    }

    public function deleteShift(Request $request, $id)
    {
        if (!$this->isOwnerUser()) return response()->json(['error' => 'forbidden'], 403);

        $actor = $this->actor();
        $agency = $this->agencyOf($actor);

        $shift = ScheduleShift::where('agency', $agency)->where('id', $id)->firstOrFail();
        $shift->delete();

        return response()->json(['ok' => true]);
    }

    public function assignShift(Request $request)
    {
        if (!$this->isOwnerUser()) return response()->json(['error' => 'forbidden'], 403);

        $actor = $this->actor();
        $agency = $this->agencyOf($actor);

        $data = $request->validate([
            'date' => 'required|date',
            'target_type' => 'required|in:user,sub',
            'target_id' => 'required|integer',
            'shift_id' => 'required|integer',
        ]);

        // valida shift de la misma agency
        $shift = ScheduleShift::where('agency', $agency)->where('id', $data['shift_id'])->firstOrFail();

        // upsert por día/persona
        $row = ScheduleAssignment::updateOrCreate(
            [
                'agency' => $agency,
                'shift_date' => $data['date'],
                'target_type' => $data['target_type'],
                'target_id' => $data['target_id'],
            ],
            [
                'shift_id' => $shift->id,
                'assigned_by_user_id' => $actor->id,
            ]
        );

        return response()->json(['ok' => true]);
    }

    public function removeAssignment(Request $request)
    {
        if (!$this->isOwnerUser()) return response()->json(['error' => 'forbidden'], 403);

        $actor = $this->actor();
        $agency = $this->agencyOf($actor);

        $data = $request->validate([
            'date' => 'required|date',
            'target_type' => 'required|in:user,sub',
            'target_id' => 'required|integer',
        ]);

        ScheduleAssignment::where('agency', $agency)
            ->where('shift_date', $data['date'])
            ->where('target_type', $data['target_type'])
            ->where('target_id', $data['target_id'])
            ->delete();

        return response()->json(['ok' => true]);
    }

    public function downloadWeekPdf(Request $request)
    {
        // Semana (si no mandas start, usamos la semana actual)
        $start = $request->query('start')
            ? Carbon::parse($request->query('start'))->startOfDay()
            : Carbon::now(config('app.timezone'))->startOfWeek(Carbon::MONDAY);

        $end = $start->copy()->addDays(6)->endOfDay();

        // agency desde sesión (ajusta si tu sesión usa otra llave)
        // ===== Agency + logo =====
        // Detectar guard real (tu app usa auth_guard en sesión)
        $guard = session('auth_guard', 'web');

        $agency = null;

        if ($guard === 'sub') {
            $subId = auth('sub')->id();
            if ($subId) {
                $agency = DB::table('sub_users')->where('id', $subId)->value('agency');
            }
        } else {
            $userId = auth('web')->id(); // o Auth::id() si tu default es web
            if ($userId) {
                $agency = DB::table('users')->where('id', $userId)->value('agency');
            }
        }

        if (!$agency) {
            abort(403, 'NO AGENCY FOUND IN SESSION (guard auth)');
        }


        // o como lo tengas en sesión (DOC-00001)

        // busca por agency_code
        $agencyRow = DB::table('agency')
            ->where('agency_code', $agency)
            ->first();


        $logoBase64 = null;

        if ($agencyRow && !empty($agencyRow->agency_logo)) {
            $relative = ltrim($agencyRow->agency_logo, '/');

            // ✅ si en DB viene solo el nombre del archivo, lo completamos
            if (!str_contains($relative, '/')) {
                $relative = 'agency_logos/' . $relative;
            }

            $candidates = [
                storage_path('app/' . $relative),     // storage/app/agency_logos/...
                base_path('storage/' . $relative),    // storage/agency_logos/...  ✅ (tu caso por la captura)
                public_path('storage/' . $relative),  // public/storage/agency_logos/...
                public_path($relative),               // public/agency_logos/...
            ];

            foreach ($candidates as $p) {
                if (file_exists($p)) {
                    $ext = strtolower(pathinfo($p, PATHINFO_EXTENSION));
                    $mime = match ($ext) {
                        'jpg', 'jpeg' => 'jpeg',
                        'png' => 'png',
                        'gif' => 'gif',
                        'webp' => 'webp',
                        default => 'png',
                    };
                    $logoBase64 = 'data:image/' . $mime . ';base64,' . base64_encode(file_get_contents($p));
                    break;
                }
            }
        }


        // ===== Asignaciones de la semana =====
        $assignments = ScheduleAssignment::with('shift')
            ->where('agency', $agency)
            ->whereBetween('shift_date', [$start->toDateString(), $end->toDateString()])
            ->get();


        // ===== Sacar lista de personas (users + sub_users) solo si aparecen en esa semana =====
        $userIds = $assignments->where('target_type', 'user')->pluck('target_id')->unique()->values();
        $subIds  = $assignments->where('target_type', 'sub')->pluck('target_id')->unique()->values();

        $users = DB::table('users')
            ->select('id', 'name')
            ->whereIn('id', $userIds)
            ->pluck('name', 'id');

        $subs = DB::table('sub_users')
            ->select('id', 'name')
            ->whereIn('id', $subIds)
            ->pluck('name', 'id');

        // ===== Construir matriz: filas = persona, columnas = día =====
        $days = collect(range(0, 6))->map(fn($i) => $start->copy()->addDays($i));

        $rows = []; // [ key => ['name'=>..., 'type'=>..., 'cells'=> [date=> cell]]]

        foreach ($assignments as $a) {
            $key = $a->target_type . ':' . $a->target_id;

            if (!isset($rows[$key])) {
                $name = $a->target_type === 'sub'
                    ? ($subs[$a->target_id] ?? ('Sub #' . $a->target_id))
                    : ($users[$a->target_id] ?? ('User #' . $a->target_id));

                $rows[$key] = [
                    'name'  => $name,
                    'type'  => $a->target_type,
                    'cells' => [],
                ];
            }

            $dateKey = Carbon::parse($a->shift_date)->toDateString();

            $text = 'No shift';
            $color = '#94a3b8';

            if ($a->shift) {
                // color guardado: "red", "purple", etc. o hex.
                // Si es nombre simple, lo usamos directo como CSS.
                $color = $a->shift->color ?: '#94a3b8';

                // ✅ dompdf a veces falla con nombres ("purple"), mejor hex
                $map = [
                    'blue'   => '#3b82f6',
                    'green'  => '#22c55e',
                    'orange' => '#f97316',
                    'purple' => '#a855f7',
                    'red'    => '#ef4444',
                    'gray'   => '#94a3b8',
                ];
                if (isset($map[strtolower($color)])) {
                    $color = $map[strtolower($color)];
                }


                if ((int)$a->shift->is_time_off === 1) {
                    $text = $a->shift->time_off_type ?: 'Time Off';
                } else {
                    $text = $a->shift->time_text ?: '—';
                }
            }

            $rows[$key]['cells'][$dateKey] = [
                'text' => $text,
                'color' => $color,
            ];
        }

        // llenar vacíos
        foreach ($rows as $k => $r) {
            foreach ($days as $d) {
                $dk = $d->toDateString();
                if (!isset($rows[$k]['cells'][$dk])) {
                    $rows[$k]['cells'][$dk] = ['text' => '', 'color' => '#e5e7eb'];
                }
            }
        }

        // ordenar filas por nombre
        $rows = collect($rows)->sortBy('name')->values();


        $pdf = Pdf::loadView('pdf.schedules_week', [
            'agency' => $agency,
            'logoBase64' => $logoBase64,
            'start' => $start,
            'end' => $end,
            'days' => $days,
            'rows' => $rows,
        ])->setPaper('letter', 'landscape');

        return $pdf->download("Schedule_{$agency}_{$start->format('Ymd')}_to_{$end->format('Ymd')}.pdf");
    }
}
