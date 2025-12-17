<?php

namespace App\Http\Controllers;

use App\Models\Reminder;
use App\Models\Customer;
use App\Models\User;
use App\Models\SubUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class RemindersController extends Controller
{
    private function currentActor()
    {
        $user = auth('web')->user();
        if ($user) return ['type' => 'user', 'model' => $user];

        $sub = auth('sub')->user();
        if ($sub) return ['type' => 'sub', 'model' => $sub];

        return null;
    }

    public function index(Request $request, $customerId)
    {
        $actor = $this->currentActor();
        if (!$actor) return redirect()->route('login');

        /** 🔹 CUSTOMER (ESTO FALTABA) */
        $customer = Customer::findOrFail($customerId);

        $agency = $actor['model']->agency;
        $q = trim($request->q ?? '');
        $perPage = in_array($request->perPage, [10, 20, 40, 50]) ? $request->perPage : 10;

        /** 🔹 USERS & SUB USERS DE LA MISMA AGENCY */
        $users = User::where('agency', $agency)->get();
        $subs  = SubUser::where('agency', $agency)->get();

        /** 🔹 REMINDERS SOLO DE ESTE CUSTOMER */
        $query = Reminder::where('agency', $agency)
            ->where('customer_id', $customer->ID);

        if ($q) {
            $query->where('description', 'like', "%$q%");
        }

        $reminders = $query->orderBy('remind_at', 'desc')
            ->paginate($perPage)
            ->appends($request->query());

        /** 🔹 Resolver nombre del remind_to */
        foreach ($reminders as $r) {
            if ($r->remind_to_type === 'user') {
                $r->remind_name = optional($users->firstWhere('id', $r->remind_to_id))->name;
            } else {
                $r->remind_name = optional($subs->firstWhere('id', $r->remind_to_id))->name;
            }
        }

        return view('reminders', compact(
            'customer',
            'reminders',
            'users',
            'subs',
            'q',
            'perPage'
        ));
    }

    public function store(Request $request, $customerId)
    {
        $actor = $this->currentActor();
        if (!$actor) return redirect()->route('login');

        $customer = Customer::findOrFail($customerId);

        $request->validate([
            'remind_at'   => 'required|date',
            'remind_to'   => 'required',
            'description' => 'required|string',
        ]);

        [$type, $id] = explode(':', $request->remind_to);

        $sendEmail = $request->has('send_email');

        $reminder = Reminder::create([
            'agency' => $actor['model']->agency,
            'customer_id' => $customer->ID,
            'remind_at' => $request->remind_at,
            'remind_to_type' => $type,
            'remind_to_id' => $id,
            'description' => $request->description,
            'send_email' => $sendEmail,
            'created_by_type' => $actor['type'],
            'created_by_id' => $actor['model']->id,
        ]);

        /** 🔹 SIMULACIÓN EMAIL */
        /* if ($sendEmail) {
            Log::info('REMINDER EMAIL SIMULATED', [
                'customer_id' => $customer->ID,
                'reminder_id' => $reminder->id,
            ]);
        }*/

        return redirect()
            ->route('reminders.index', $customer->ID)
            ->with('success', 'Reminder saved successfully.');
    }


    public function processPendingReminders()
    {
        $now = now();

        $pending = \App\Models\Reminder::where('send_email', 1)
            ->where('remind_at', '<=', $now)
            ->whereNull('notified_at') // 👈 ver nota abajo
            ->get();

        foreach ($pending as $r) {
            Log::info('REMINDER EMAIL SENT (SIMULATED)', [
                'reminder_id' => $r->id,
                'customer_id' => $r->customer_id,
                'remind_at' => $r->remind_at,
            ]);

            // Marcamos como notificado
            $r->update([
                'notified_at' => now()
            ]);
        }
    }

    public function simulateReminderEmails()
    {
        $now = now();

        $reminders = Reminder::where('send_email', 1)
            ->whereNull('notified_at')
            ->where('remind_at', '<=', $now)
            ->get();

        foreach ($reminders as $r) {

            Log::info('REMINDER EMAIL SENT (SIMULATED)', [
                'reminder_id' => $r->id,
                'customer_id' => $r->customer_id,
                'remind_at'   => $r->remind_at,
                'sent_at'     => $now->toDateTimeString(),
            ]);

            // Marcamos como ya notificado
            $r->update([
                'notified_at' => $now
            ]);
        }
    }

    public function destroy($customerId, $reminderId)
    {
        $actor = $this->currentActor();
        if (!$actor) return response()->json(['error' => 'Unauthorized'], 401);

        $customer = Customer::findOrFail($customerId);

        $reminder = Reminder::where('id', $reminderId)
            ->where('customer_id', $customer->ID)
            ->where('agency', $actor['model']->agency)
            ->firstOrFail();

        $reminder->delete();

        return response()->json(['success' => true]);
    }
}
