<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class CheckUserStatus
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {

        if (!Auth::guest()) {
            // if user was deactivated while logged in, boot them out
            if (\Auth::user()->status != 'active') {
                Auth::logout();
                return redirect('/login');
            }
        }

        return $next($request);
    }
}
