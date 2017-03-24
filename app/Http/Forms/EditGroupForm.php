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
use App\Extensions\Constraints as CustomAssert;
use App\Models\Group;
use App\Models\User;

class EditGroupForm extends Form
{
    protected $permissions = [
        'All'                       => '*',
        'Can Manage Groups'         => 'manage_groups',
        'Can Manage Users'          => 'manage_users',
        'Can Manage Leave Settings' => 'manage_leave'
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
