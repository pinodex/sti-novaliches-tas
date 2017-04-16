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
        if (Auth::user()->last_password_change_at == null) {
            return redirect()->route('account.settings.index');
        }

        return $next($request);
    }
}
