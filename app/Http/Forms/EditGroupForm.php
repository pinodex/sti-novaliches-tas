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
use App\Components\Acl;
use App\Models\Group;
use App\Models\User;

class EditGroupForm extends Form
{
    protected $permissions = [
        'All'                               => '*',
        'Can Administer Requests'           => Acl::ADMIN_REQUESTS,
        'Can Submit Requests'               => Acl::SUBMIT_REQUESTS,
        'Can Approve/Disapprove Requests'   => Acl::APPROVE_DISAPPROVE_REQUESTS,
        'Can Administer Bulletin'           => Acl::ADMIN_BULLETIN,
        'Can Administer Groups'             => Acl::ADMIN_GROUPS,
        'Can Administer Users'              => Acl::ADMIN_USERS,
        'Can Administer Profiles'           => Acl::ADMIN_PROFILES,
        'Can Edit Configuration'            => Acl::ADMIN_CONFIGURATION
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
