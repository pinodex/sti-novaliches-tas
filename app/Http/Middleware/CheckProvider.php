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

class CheckProvider
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, $provider)
    {
        $providers = Auth::getProvider()->getProviders();

        if (array_key_exists($provider, $providers)) {
            $providingModel = $providers[$provider]::getProvidingModel();

            if (get_class(Auth::user()) == $providingModel) {
                return $next($request);
            }
        }

        abort(401);
    }
}
