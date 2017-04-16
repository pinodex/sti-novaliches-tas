<?php

/*
 * This file is part of the TAS System for STI College Novaliches
 *
 * (c) Raphael Marco <raphaelmarco@outlook.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Components\MultiAuth;

use Illuminate\Contracts\Auth\UserProvider as BaseUserProvider;
use Illuminate\Contracts\Auth\Authenticatable;

class BaseProvider implements BaseUserProvider
{
    /**
     * @var array Array of model providers for auth
     */
    protected $providers = [];

    public function __construct($providers = [])
    {
        $this->providers = $providers;
    }

    public function retrieveById($identifier)
    {
        $identifier = explode(':', $identifier);

        $providerName = $identifier[0];
        $userId = $identifier[1];

        if (array_key_exists($providerName, $this->providers)) {
            $provider = new $this->providers[$providerName];
            
            return $provider->retrieveById($userId);
        }

        return null;
    }

    public function retrieveByToken($identifier, $token)
    {

    }

    public function updateRememberToken(Authenticatable $user, $token)
    {

    }

    public function retrieveByCredentials(array $credentials)
    {
        foreach ($this->providers as $class) {
            $provider = new $class;
            $user = $provider->retrieveByCredentials($credentials);

            if ($user) {
                return $user;
            }
        }
    }

    public function validateCredentials(Authenticatable $user, array $credentials)
    {
        foreach ($this->providers as $class) {
            if (get_class($user) != $class::getProvidingModel()) {
                continue;
            }

            $provider = new $class;
            $result = $provider->validateCredentials($user, $credentials);

            if ($result) {
                return true;
            }
        }

        return false;
    }
}
