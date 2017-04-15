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
use App\Models\Employee;

class EditDepartmentForm extends Form
{
    public function create()
    {
        $employees = Employee::all();

        $this->add('name', Type\TextType::class, [
            'attr' => [
                'autofocus' => true
            ]
        ]);

        $this->add('head_id', Type\ChoiceType::class, [
            'label'     => 'Head',
            'choices'   => $this->toChoices($employees, true)
        ]);
    }
}
