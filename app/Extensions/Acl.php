<?php

/*
 * This file is part of the TAS System for STI College Novaliches
 *
 * (c) Raphael Marco <raphaelmarco@outlook.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Extensions;

use App\Models\User;

class Acl
{
    const MANAGE_REQUESTS = 'manage_requests';

    const SUBMIT_REQUESTS = 'submit_requests';

    const MANAGE_BULLETIN = 'manage_bulletin';

    const MANAGE_GROUPS = 'manage_groups';

    const MANAGE_USERS = 'manage_users';

    const MANAGE_LEAVE = 'manage_leave';

    private $permissions;

    public function __construct(User $user)
    {
        $this->permissions = $user->group->permissions;
    }

    public static function for(User $user)
    {
        return new self($user);
    }

    public function can()
    {
        $args = func_get_args();

        if ($this->permissions == null) {
            return false;
        }

        if (array_search('*', $this->permissions) !== false) {
            return true;
        }

        foreach ($args as $requestedPermission) {
            foreach ($this->permissions as $allowedPermission) {
                if (fnmatch($requestedPermission, $allowedPermission)) {
                    return true;
                }
            }
        }

        return false;
    }
}
