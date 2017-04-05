<?php

/*
 * This file is part of the TAS System for STI College Novaliches
 *
 * (c) Raphael Marco <raphaelmarco@outlook.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Http\Controllers;

use Auth;
use Illuminate\Http\Request;
use App\Http\Forms\LoginForm;

class AuthController extends Controller
{
    /**
     * Login page
     * 
     * @param \Illuminate\Http\Request $request Request object
     * 
     * @return mixed
     */
    public function login(Request $request)
    {
        $form = with(new LoginForm)
            ->getForm()
            ->handleRequest($request);

        if ($form->isValid()) {
            $data = $form->getData();
            $auth = Auth::attempt($data);

            if (!$auth) {
                return redirect()->route('auth.login')->withErrors([
                    'message' => 'Invalid username and/or password'
                ]);
            }

            return redirect()->route('index');
        }

        return view('auth.login', [
            'form' => $form->createView()
        ]);
    }

    /**
     * Logout action
     * 
     * @return mixed
     */
    public function logout()
    {
        Auth::logout();

        return redirect()->route('auth.login');
    }
}
