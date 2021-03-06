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
use App\Models\Department;
use App\Models\Profile;
use App\Models\Group;
use App\Models\User;

class EditUserForm extends Form
{
    public function create()
    {
        $departments = Department::all();
        $profiles = Profile::all();
        $groups = Group::all();

        $this->add('picture', Type\FileType::class, [
            'label'         => 'Picture (leave if not changing)',
            'constraints'   => new Assert\Image(),
            'attr'          => [
                'accept'    => 'image/*'
            ],
            'required'      => false
        ]);

        $this->add('first_name', Type\TextType::class);

        $this->add('middle_name', Type\TextType::class, [
            'required'  => false,
            'label'     => 'Middle name (optional)'
        ]);

        $this->add('last_name', Type\TextType::class);
        
        $this->add('username', Type\TextType::class, [
            'constraints' => [
                new CustomAssert\UniqueRecord([
                    'model'     => User::class,
                    'exclude'   => $this->model ? $this->model->username : null,
                    'row'       => 'username',
                    'message'   => 'Username already in use.'
                ]),

                new Assert\Regex([
                    'pattern'   => '/^[A-Za-z0-9]+((_|\.)[A-Za-z0-9]+)*$/',
                    'match'     => true,
                    'message'   => 'Username can only contain alphanumeric characters, underscores, and dots'
                ]),

                new Assert\Length([
                    'min'           => 4,
                    'max'           => 32,
                    'minMessage'    => 'Username must contain at least 4 characters',
                    'maxMessage'    => 'Username cannot be more than 32 characters'
                ])
            ]
        ]);
        
        $this->add('email', Type\EmailType::class, [
            'constraints' => new CustomAssert\UniqueRecord([
                'model'     => User::class,
                'exclude'   => $this->model ? $this->model->email : null,
                'row'       => 'email',
                'message'   => 'Email address already in use.'
            ])
        ]);
        
        $this->add('password', Type\RepeatedType::class, [
            'type'              => Type\PasswordType::class,
            'first_options'     => ['label' => 'Password (leave blank if not changing)'],
            'second_options'    => ['label' => 'Repeat Password (leave blank if not changing)'],
            'required'          => $this->model === null,
                
            'constraints'       => new Assert\Length([
                'min'           => 8,
                'minMessage'    => 'Password should be at least 8 characters long.'
            ])
        ]);

        $this->add('require_password_change', Type\CheckboxType::class, [
            'label'             => 'Require password change on login',
            'required'          => false
        ]);

        $this->add('department_id', Type\ChoiceType::class, [
            'label'     => 'Department',
            'choices'   => $this->toChoices($departments, true)
        ]);

        $this->add('group_id', Type\ChoiceType::class, [
            'label'     => 'Group',
            'choices'   => $this->toChoices($groups, true)
        ]);

        $this->add('profile_id', Type\ChoiceType::class, [
            'label'     => 'Profile',
            'choices'   => $this->toChoices($profiles, true)
        ]);
    }
}
