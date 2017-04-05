<?php

namespace App\Http\Middleware;

use Auth;
use Closure;

class RequirePasswordChange
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
        if (Auth::user()->require_password_change) {
            return redirect()->route('account.settings.index');
        }

        return $next($request);
    }
}
