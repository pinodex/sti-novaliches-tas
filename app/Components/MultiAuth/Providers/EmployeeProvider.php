<?php

/*
 * This file is part of the TAS System for STI College Novaliches
 *
 * (c) Raphael Marco <raphaelmarco@outlook.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Components\MultiAuth\Providers;

use Hash;
use Illuminate\Contracts\Auth\Authenticatable;
use App\Models\Employee;

class EmployeeProvider implements ProviderInterface
{
    public static function getProvidingModel()
    {
        return Employee::class;
    }

    public function retrieveById($identifier)
    {
        return Employee::find($identifier);
    }

    public function retrieveByToken($identifier, $token)
    {

    }

    public function updateRememberToken(Authenticatable $user, $token)
    {

    }

    public function retrieveByCredentials(array $credentials)
    {
        return Employee::where('username', $credentials['username'])->first();
    }

    public function validateCredentials(Authenticatable $user, array $credentials)
    {
        if (Hash::check($credentials['password'], $user->password)) {
            $user->timestamps = false;

            if (Hash::needsRehash($user->password)) {
                $user->password = Hash::make($credentials['password']);
            }

            $user->last_login_at = date('Y-m-d H:i:s');
            $user->save();

            return true;
        }

        return false;
    }
}
