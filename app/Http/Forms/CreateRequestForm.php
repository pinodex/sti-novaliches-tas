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
use App\Models\Department;
use App\Models\Request;
use App\Models\Employee;

class CreateRequestForm extends Form
{
    protected $requestor;

    protected $approver;

    protected $types = [];

    public function __construct(Employee $requestor, Request $model = null)
    {
        $this->requestor = $requestor;
        $this->approver = $requestor->department->head;

        parent::__construct($model);
    }

    public function create()
    {
        $this->add('requestor', Type\TextType::class, [
            'data'      => $this->requestor->name,

            'attr' => [
                'readonly'  => true
            ]
        ]);

        $this->add('approver', Type\TextType::class, [
            'data'      => $this->approver->name,

            'attr' => [
                'readonly'  => true
            ]
        ]);

        $this->add('type_id', Type\ChoiceType::class, [
            'label'     => 'Type',
            'choices'   => array_flip($this->types)
        ]);

        $this->add('from_date', Type\DateTimeType::class, [
            'label'         => 'From',
            'required'      => false,
            'html5'         => true,
            'input'         => 'string',
            'date_widget'   => 'single_text',
            'time_widget'   => 'single_text'
        ]);

        $this->add('to_date', Type\DateTimeType::class, [
            'label'         => 'To',
            'required'      => false,
            'html5'         => true,
            'input'         => 'string',
            'date_widget'   => 'single_text',
            'time_widget'   => 'single_text'
        ]);

        $this->add('reason', Type\TextareaType::class);
    }
}
