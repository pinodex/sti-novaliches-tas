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
use Password;
use Illuminate\Http\Request;
use App\Http\Forms\LoginForm;
use App\Http\Forms\PasswordResetForm;
use App\Models\User;

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
                return redirect()->route('auth.login')
                    ->with('message', ['danger', 'Invalid username and/or password']);
            }

            if ($next = $request->query->get('next')) {
                return redirect($request->getSchemeAndHttpHost() . '/' . ltrim(urldecode($next), '/'));
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

    /**
     * Password reset page
     * 
     * @return mixed
     */
    public function reset(Request $request, $email, $token)
    {
        $user = User::where('email', $email)->first();

        if (!$user) {
            return redirect()->route('auth.login')
                ->with('message', ['danger', 'Invalid password reset link']);
        }

        $isExists = Password::getRepository()->exists($user, $token);

        if (!$isExists) {
            return redirect()->route('auth.login')
                ->with('message', ['danger', 'Invalid password reset link']);
        }

        $form = with(new PasswordResetForm)
            ->getForm()
            ->handleRequest($request);

        if ($form->isValid()) {
            $data = $form->getData();

            $user->password = $data['password'];
            $user->require_password_change = false;
            $user->timestamps = false;

            $user->save();

            Password::getRepository()->delete($user);

            $user->log('reset_password');

            return redirect()->route('auth.login')
                ->with('message', ['success', 'Your new password has been successfully set']);
        }

        return view('auth.reset', [
            'form' => $form->createView(),
            'user' => $user
        ]);
    }
}
