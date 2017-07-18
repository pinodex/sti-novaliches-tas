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

use Auth;
use DateTime;
use Illuminate\Http\Request;
use App\Http\Forms\LoginForm;
use App\Http\Controllers\Controller;
use App\Models\Sso\Client as SsoClient;
use App\Components\Sso\Responses\TokenResponse;
use League\Uri\Schemes\Http as HttpUri;
use League\Uri\Modifiers\MergeQuery;

class AuthorizeController extends Controller
{
    /**
     * Authorize page for SSO client
     * 
     * @param \Illuminate\Http\Request $request Request object
     * 
     * @return mixed
     */
    public function __invoke(Request $request)
    {
        $client = SsoClient::find($request->input('client_id'));
        $username = $request->input('username');

        if (!$client) {
            return redirect()->route('auth.login');
        }

        $form = with(new LoginForm)
            ->getForm()
            ->handleRequest($request);

        if ($form->isValid()) {
            $data = $form->getData();
            $auth = Auth::attempt($data);

            if (!$auth) {
                return redirect()->route('sso.login', [
                    'client_id'     => $request->input('client_id')
                ])->with('message', ['danger', 'Invalid username and/or password']);
            }

            $user = Auth::user();
            $expiry = new DateTime();
            $expiry->modify('+1 week');

            $token = $client->generateToken($user, $expiry);
            $token->save();

            $user->log('sso_authorized_client', [
                'name' => $client->name
            ]);

            $baseUri = HttpUri::createFromString($client->redirect_uri);
            $tokenQuery = new MergeQuery('code=' . $token->code);

            $redirectUri = $tokenQuery->process($baseUri);

            return redirect((string) $redirectUri);
        }

        return view('sso.authorize', [
            'form'      => $form->createView(),
            'client'    => $client,
            'username'  => $username
        ]);
    }
}
