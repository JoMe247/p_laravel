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

    $tasks = DB::table('tasks')
        ->where('agency', $agency)
        ->orderBy('id', 'desc')
        ->get();

    foreach ($tasks as $t) {
        if ($t->assigned_user_type === 'user') {
            $u = User::find($t->assigned_user_id);
        } else {
            $u = SubUser::find($t->assigned_user_id);
        }

        $t->assigned_name = $u ? $u->name : 'Unknown';
    }

    $assignees = collect(User::where('agency',$agency)->get()
        ->map(fn($u)=>['type'=>'user','id'=>$u->id,'name'=>$u->name])
        ->merge(
            SubUser::where('agency',$agency)->get()
                ->map(fn($s)=>['type'=>'sub_user','id'=>$s->id,'name'=>$s->name])
        ));

    return view('tasks', compact('tasks','assignees'));
}

public function store(Request $r)
{
    $auth = auth('web')->user() ?? auth('sub')->user();
    [$type, $assigned] = explode('|', $r->assigned);

    DB::table('tasks')->insert([
        'agency' => $auth->agency,
        'subject' => $r->subject,
        'start_date' => $r->start_date,
        'due_date' => $r->due_date,
        'priority' => $r->priority,
        'status' => 'Open',
        'assigned_user_type' => $type,
        'assigned_user_id' => $assigned,
        'description' => $r->description,
        'created_at' => now(),
        'updated_at' => now()
    ]);

    return redirect()->route('tasks.index');
}

public function updateStatus(Request $r)
{
    DB::table('tasks')->where('id',$r->id)->update([
        'status'=>$r->status
    ]);
    return response()->json(['ok'=>true]);
}

public function updatePriority(Request $r)
{
    DB::table('tasks')->where('id',$r->id)->update([
        'priority'=>$r->priority
    ]);
    return response()->json(['ok'=>true]);
}

public function delete(Request $r)
{
    DB::table('tasks')->where('id',$r->id)->delete();
    return response()->json(['ok'=>true]);
}



}
