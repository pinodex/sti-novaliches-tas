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

    /**
     * @var array Allowed permissions
     */
    private $permissions;

    /**
     * Constructs Acl
     * 
     * @param \App\Models\User $user User instance
     */
    public function __construct(User $user)
    {
        $this->permissions = $user->group->permissions;
    }

    /**
     * Get Acl instance for user
     * 
     * @param \App\Models\User $user User instance
     * 
     * @return \App\Extensions\Acl Acl instance
     */
    public static function for(User $user)
    {
        return new self($user);
    }

    /**
     * Check if user has granted permissions
     * 
     * @param string $permissions,... Permission name
     * 
     * @return boolean
     */
    public function can(...$permissions)
    {
        if ($this->permissions == null) {
            return false;
        }

        if (array_search('*', $this->permissions) !== false) {
            return true;
        }

        foreach ($permissions as $requestedPermission) {
            foreach ($this->permissions as $allowedPermission) {
                if (fnmatch($requestedPermission, $allowedPermission)) {
                    return true;
                }
            }
        }

        return false;
    }
}
