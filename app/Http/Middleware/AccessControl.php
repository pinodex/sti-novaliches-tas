<?php

/*
 * This file is part of the TAS System for STI College Novaliches
 *
 * (c) Raphael Marco <raphaelmarco@outlook.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Http\Middleware;

use Auth;
use Closure;

class AccessControl
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, $args)
    {
        if (!Auth::check()) {
            abort(401);
        }

        $user = Auth::user();

        if ($user->group->permissions == null)
        {
            abort(403);
        }

        if (array_search('*', $user->group->permissions) !== false) {
            return $next($request);
        }

        $requestedPermissions = explode(',', $args);

        foreach ($requestedPermissions as $requestedPermission) {
            foreach ($user->group->permissions as $allowedPermission) {
                if (fnmatch($requestedPermission, $allowedPermission)) {
                    return $next($request);
                }
            }
        }

        abort(403);
    }
}
