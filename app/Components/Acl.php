<?php

/*
 * This file is part of the TAS System for STI College Novaliches
 *
 * (c) Raphael Marco <raphaelmarco@outlook.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Components;

use App\Models\User;

class Acl
{
    const ALL = '*';
    const ADMIN_REQUESTS = 'admin_requests';
    const SUBMIT_REQUESTS = 'submit_requests';
    const ADMIN_BULLETIN = 'admin_bulletin';
    const ADMIN_GROUPS = 'admin_groups';
    const ADMIN_USERS = 'admin_users';
    const ADMIN_DEPARTMENTS = 'admin_departments';
    const ADMIN_EMPLOYEES = 'admin_employees';
    const ADMIN_PROFILES = 'admin_profiles';
    const ADMIN_CONFIGURATION = 'admin_configuration';
    const APPROVE_DISAPPROVE_REQUESTS = 'approve_disapprove_requests';

    /**
     * @var array Allowed permissions
     */
    protected $permissions = [];

    /**
     * Constructs Acl
     * 
     * @param \App\Models\User $user User instance
     */
    public function __construct(User $user)
    {
        if ($user->group) {
            $this->permissions = $user->group->permissions;
        }
    }

    /**
     * Get Acl instance for user
     * 
     * @param \App\Models\User $user User instance
     * 
     * @return \App\Components\Acl Acl instance
     */
    public static function for(User $user)
    {
        return new static($user);
    }

    /**
     * Check if user has granted permissions
     * 
     * @param array|string $permissions Permission names
     * 
     * @return boolean
     */
    public function can($permissions)
    {
        if (!is_array($permissions)) {
            $permissions = [$permissions];
        }
        
        if (array_search('*', $permissions) !== false) {
            return true;
        }

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
