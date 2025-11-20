<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\SubUser;

class HelpController extends Controller
{
    public function index()
    {
        $current = auth('web')->user() ?? auth('sub')->user();
        $agency = $current->agency;

        $users     = User::where('agency', $agency)->get();
        $subusers  = SubUser::where('agency', $agency)->get();

        return view('help', compact('users', 'subusers'));
    }
}
