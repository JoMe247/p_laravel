<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\Response;

class UpdateUserLastSeen
{
    public function handle(Request $request, Closure $next): Response
    {
        /*
        |--------------------------------------------------------------------------
        | User principal
        |--------------------------------------------------------------------------
        */
        if (Auth::guard('web')->check()) {
            $user = Auth::guard('web')->user();

            if (!$user->last_seen_at || Carbon::parse($user->last_seen_at)->lt(now()->subSeconds(60))) {
                DB::table('users')
                    ->where('id', $user->id)
                    ->update([
                        'last_seen_at' => now(),
                    ]);
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Sub user
        |--------------------------------------------------------------------------
        */
        if (Auth::guard('sub')->check()) {
            $subUser = Auth::guard('sub')->user();

            if (!$subUser->last_seen_at || Carbon::parse($subUser->last_seen_at)->lt(now()->subSeconds(60))) {
                DB::table('sub_users')
                    ->where('id', $subUser->id)
                    ->update([
                        'last_seen_at' => now(),
                    ]);
            }
        }

        return $next($request);
    }
}