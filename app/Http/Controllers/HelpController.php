<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\SubUser;
use App\Models\Ticket;

class HelpController extends Controller
{
    public function index()
{
    $auth = auth('web')->user() ?? auth('sub')->user();
    $agency = $auth->agency;

    $users = User::where('agency', $agency)->get();
    $subusers = SubUser::where('agency', $agency)->get();

    $tickets = Ticket::where('agency', $agency)
        ->orderBy('id', 'desc')
        ->get();

    return view('help', compact('users', 'subusers', 'tickets'));
}


    public function store(Request $request)
    {
        $auth = auth('web')->user() ?? auth('sub')->user();

        $request->validate([
            'subject' => 'required|string|max:255',
            'assigned_to' => 'nullable|string',
            'priority' => 'required|string',
            'date' => 'required|date',
            'status' => 'required|string'
        ]);

        // Parse assigned user
        $assigned_type = null;
        $assigned_id = null;

        if ($request->assigned_to) {
            [$assigned_type, $assigned_id] = explode('-', $request->assigned_to);
        }

        Ticket::create([
            'agency' => $auth->agency,
            'created_by_type' => $auth instanceof \App\Models\User ? 'user' : 'sub_user',
            'created_by_id' => $auth->id,

            'subject' => $request->subject,
            'priority' => $request->priority,
            'status' => $request->status,
            'date' => $request->date,

            'assigned_type' => $assigned_type,
            'assigned_id' => $assigned_id,
        ]);

        return back()->with('success', 'Ticket created!');
    }
}
