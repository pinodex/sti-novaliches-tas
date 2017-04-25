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
use App\Models\Employee;
use App\Models\Request;

class EditRequestForm extends Form
{
    public function create()
    {
        $employees = Employee::all();

        $this->add('approver_id', Type\ChoiceType::class, [
            'label'     => 'Approver',
            'choices'   => $this->toChoices($employees),
            'required'  => false
        ]);

        $this->add('status', Type\ChoiceType::class, [
            'label'     => 'Status',
            'choices'   => array_flip(Request::$statusLabels)
        ]);

        $this->add('disapproval_reason', Type\TextareaType::class, [
            'required' => false
        ]);
    }
}
