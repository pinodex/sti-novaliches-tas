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
use App\Models\User;

class EditUserForm extends Form
{
    public function create()
    {
        $this->add('name', Type\TextType::class, [
            'attr' => [
                'autofocus' => true
            ]
        ]);
        
        $this->add('username', Type\TextType::class, [
            'constraints' => new CustomAssert\UniqueRecord([
                'model'     => User::class,
                'exclude'   => $this->model ? $this->model->username : null,
                'row'       => 'username',
                'message'   => 'Username already in use.'
            ])
        ]);
        
        $this->add('email', Type\EmailType::class);
        
        $this->add('password', Type\RepeatedType::class, [
            'type'              => Type\PasswordType::class,
            'first_options'     => ['label' => 'Password'],
            'second_options'    => ['label' => 'Repeat Password'],
                
            'constraints'       => new Assert\Length([
                'min'           => 8,
                'minMessage'    => 'Password should be at least 8 characters long.'
            ])
        ]);
    }
}
