<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class ForcePasswordChange
{
    public function handle($request, Closure $next)
    {
        if (
            Auth::check() &&
            Auth::user()->user_type === 'applicant' &&
            Auth::user()->must_change_password &&
            !$request->is('logout') &&
            !$request->is('change-password')
        ) {
            return redirect()->route('password.change.form');
        }

        return $next($request);
    }
}
