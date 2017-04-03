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
use App\Models\Department;
use App\Models\Group;
use App\Models\User;

class EditUserForm extends Form
{
    public function setData($data)
    {
        if (!array_key_exists('departments', $data)) {
            return parent::setData($data);
        }

        $data['departments'] = array_column($data['departments'], 'id');
        return parent::setData($data);
    }

    public function create()
    {
        $groups = Group::all();
        $departments = Department::all();

        $this->add('name', Type\TextType::class, [
            'attr' => [
                'autofocus' => true
            ]
        ]);
        
        $this->add('username', Type\TextType::class, [
            'constraints' => [
                new CustomAssert\UniqueRecord([
                    'model'     => User::class,
                    'exclude'   => $this->model ? $this->model->username : null,
                    'row'       => 'username',
                    'message'   => 'Username already in use.'
                ]),

                new Assert\Regex([
                    'pattern'   => '/^[A-Za-z0-9]+(-[A-Za-z0-9]+)*$/',
                    'match'     => true,
                    'message'   => 'Username can only contain alphanumeric characters and dashes'
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
                'message'   => 'Email already in use.'
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

        $this->add('group_id', Type\ChoiceType::class, [
            'choices'   => $this->toChoices($groups, true)
        ]);

        $this->add('departments', Type\ChoiceType::class, [
            'choices'   => $this->toChoices($departments),
            'multiple'  => true,
            'required'  => false
        ]);
    }
}
