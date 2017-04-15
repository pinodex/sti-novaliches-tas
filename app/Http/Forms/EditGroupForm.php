<?php

/*
 * This file is part of the TAS System for STI College Novaliches
 *
 * (c) Raphael Marco <raphaelmarco@outlook.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Http\Forms;

use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Validator\Constraints as Assert;
use App\Constraints as CustomAssert;
use App\Extensions\Acl;
use App\Models\Group;
use App\Models\User;

class EditGroupForm extends Form
{
    protected $permissions = [
        'All'                       => '*',
        'Can Manage Requests'       => Acl::MANAGE_REQUESTS,
        'Can Submit Requests'       => Acl::SUBMIT_REQUESTS,
        'Can Manage Bulletin'       => Acl::MANAGE_BULLETIN,
        'Can Manage Groups'         => Acl::MANAGE_GROUPS,
        'Can Manage Users'          => Acl::MANAGE_USERS,
        'Can Manage Leave Settings' => Acl::MANAGE_LEAVE
    ];

    public function create()
    {
        $groups = Group::all();

        $this->add('name', Type\TextType::class, [
            'attr' => [
                'autofocus' => true
            ]
        ]);

        $this->add('permissions', Type\ChoiceType::class, [
            'multiple'  => true,
            'expanded'  => true,
            'choices'   => $this->permissions,
            'required'  => false
        ]);
    }
}
