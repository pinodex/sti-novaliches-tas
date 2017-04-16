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

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

abstract class AbstractUser extends Model implements Authenticatable
{
    /**
     * Get redirect action when logged in
     * 
     * @return \Illuminate\Http\RedirectResponse
     */
    abstract public function getRedirectAction();

    /**
     * Check if user has granted permissions
     * 
     * @param string $permissions,... Permission name
     * 
     * @return boolean
     */
    abstract public function canDo($permissions);
}
