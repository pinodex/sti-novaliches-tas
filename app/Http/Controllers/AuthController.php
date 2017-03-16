<?php

namespace App\Http\Controllers;

use Auth;
use App\Extensions\Form;
use Illuminate\Http\Request;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\Extension\Core\Type;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $form = Form::create()
            ->add('username', Type\TextType::class, [
                'attr'  => [
                    'autofocus' => true
                ]
            ])
            ->add('password', Type\PasswordType::class)
            ->getForm();

        $form->handleRequest($request);

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
}
