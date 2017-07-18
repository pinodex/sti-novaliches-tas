<?php

/*
 * This file is part of the TAS System for STI College Novaliches
 *
 * (c) Raphael Marco <raphaelmarco@outlook.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Components\Sso\Responses;

use App\Models\User;

class IdentityResponse extends Response
{
    public function __construct(User $user, $status = 200, $headers = [], $options = 0)
    {
        $user->load('group', 'department', 'department.head', 'profile');

        $data = [
            'id'            => $user->id,
            'first_name'    => $user->first_name,
            'middle_name'   => $user->middle_name,
            'last_name'     => $user->last_name,
            'name'          => $user->name,
            'username'      => $user->username,
            'email'         => $user->email,
            'picture'       => $user->picture,
            'group'         => null,
            'department'    => null
        ];

        if ($user->group) {
            $data['group'] = [
                'id'            => $user->group->id,
                'name'          => $user->group->name,
                'permissions'   => $user->group->permissions
            ];
        }

        if ($user->department) {
            $data['department'] = [
                'id'            => $user->department->id,
                'name'          => $user->department->name
            ];

            if ($user->department->head) {
                $data['department']['head'] = [
                    'id'            => $user->department->head->id,
                    'first_name'    => $user->department->head->first_name,
                    'middle_name'   => $user->department->head->middle_name,
                    'last_name'     => $user->department->head->last_name,
                    'name'          => $user->department->head->name
                ];
            }
        }

        parent::__construct($data, $status, $headers, $options);
    }
}
