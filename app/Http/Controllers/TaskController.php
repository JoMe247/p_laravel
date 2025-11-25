<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\SubUser;
use Illuminate\Support\Facades\DB;

class TaskController extends Controller
{
    public function index()
    {
        $auth = auth('web')->user() ?? auth('sub')->user();
        $agency = $auth->agency;

        // Obtener tasks
        $tasks = DB::table('tasks')
            ->where('agency', $agency)
            ->orderBy('id', 'desc')
            ->get();

        // Obtener nombres del assigned
        foreach ($tasks as $t) {
            if ($t->assigned_user_type === 'user') {
                $u = User::find($t->assigned_user_id);
            } else {
                $u = SubUser::find($t->assigned_user_id);
            }

            $t->assigned_name = $u ? $u->name : 'Unknown';
        }

        // Obtener users y sub users como assignees
        $assignees = [];

        $users = User::where('agency', $agency)->get();
        foreach ($users as $u) {
            $assignees[] = ['type' => 'user', 'id' => $u->id, 'name' => $u->name];
        }

        $subs = SubUser::where('agency', $agency)->get();
        foreach ($subs as $s) {
            $assignees[] = ['type' => 'sub_user', 'id' => $s->id, 'name' => $s->name];
        }

        return view('tasks', compact('tasks', 'assignees'));
    }

    public function store(Request $request)
    {
        $auth = auth('web')->user() ?? auth('sub')->user();
        $agency = $auth->agency;

        [$type, $assignedId] = explode('|', $request->assigned);

        DB::table('tasks')->insert([
            'agency' => $agency,
            'subject' => $request->subject,
            'start_date' => $request->start_date,
            'due_date' => $request->due_date,
            'priority' => $request->priority,
            'assigned_user_id' => $assignedId,
            'assigned_user_type' => $type,
            'description' => $request->description,
            'created_by' => $auth->id,
            'created_by_type' => $auth instanceof User ? 'user' : 'sub_user'
        ]);

        return redirect()->route('tasks.index');
    }
}
