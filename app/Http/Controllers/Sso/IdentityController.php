<?php

/*
 * This file is part of the TAS System for STI College Novaliches
 *
 * (c) Raphael Marco <raphaelmarco@outlook.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Http\Controllers\Sso;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Components\Sso\Responses\IdentityResponse;

class IdentityController extends Controller
{
    /**
     * Returns authenticated user's identity
     * 
     * @param \Illuminate\Http\Request $request Request object
     * 
     * @return mixed
     */
    public function index(Request $request)
    {
        return new IdentityResponse($request->ssouser);
    }

    /**
     * Redirect to user picture
     * 
     * @param \Illuminate\Http\Request $request Request object
     * 
     * @return mixed
     */
    public function picture(Request $request)
    {
        return $request->ssouser->picture;
    }

    /**
     * Redirect to user picture image
     * 
     * @param \Illuminate\Http\Request $request Request object
     * 
     * @return mixed
     */
    public function pictureImage(Request $request)
    {
        $uri = $request->ssouser->picture['image'];

        if ($request->input('redirect') == 1) {
            return redirect($uri);
        }

        return [
            'uri'   => $uri
        ];
    }

    /**
     * Redirect to user picture thumb
     * 
     * @param \Illuminate\Http\Request $request Request object
     * 
     * @return mixed
     */
    public function pictureThumb(Request $request)
    {
        $uri = $request->ssouser->picture['thumb'];

        if ($request->input('redirect') == 1) {
            return redirect($uri);
        }

        return [
            'uri'   => $uri
        ];
    }
}
