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

class SettingsForm extends Form
{
    public function create()
    {
        $this->add('current_password', Type\PasswordType::class, [
            'constraints' => new CustomAssert\PasswordMatch([
                'hash' => $this->model->password
            ])
        ]);

        $this->add('password', Type\RepeatedType::class, [
            'type'              => Type\PasswordType::class,
            'first_options'     => ['label' => 'New Password'],
            'second_options'    => ['label' => 'Repeat Password'],
            
            'constraints'       => new Assert\Length([
                'min'           => 8,
                'minMessage'    => 'Password should be at least 8 characters long.'
            ])
        ]);
    }
}
