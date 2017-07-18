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

use Config;
use Closure;
use DateTime;
use App\Components\Sso\AccessToken;
use App\Components\Sso\Responses\ErrorResponse;
use App\Components\Sso\TokenAuthorizationException;
use App\Models\Sso\Client;
use App\Models\Sso\Token;

class SsoAuthenticate
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
        Config::set('session.driver', 'array');
        Config::set('cookie.driver', 'array');

        $authorizationHeader = $request->headers->get('Authorization');

        try {
            $accessToken = new AccessToken($authorizationHeader);
        } catch (TokenAuthorizationException $e) {
            return new ErrorResponse($e->getMessage(), ErrorResponse::GENERIC_ERROR, 401);
        }

        $client = Client::find($accessToken->getClientId());

        if (!$client || !$client->validateSecret($accessToken->getClientSecret())) {
            return new ErrorResponse('Unable to validate the client', ErrorResponse::INVALID_CLIENT, 401);
        }

        $token = $client->tokens()->where('code', $accessToken->getCode())->first();

        if (!$token) {
            return new ErrorResponse('Unable to validate the supplied code', ErrorResponse::INVALID_CODE, 401);
        }

        if ($token->expires_at) {
            $now = new DateTime();
            $expirationDate = new DateTime($token->expires_at);

            if ($now > $expirationDate) {
                return new ErrorResponse('The authorization code has already expired', ErrorResponse::EXPIRED_CODE, 401);
            }
        }

        $token->touch();

        $request->ssouser = $token->user;

        return $next($request);
    }
}
