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
use App\Models\LeaveBalance;
use App\Models\Department;
use App\Models\Request;
use App\Models\User;

class CreateRequestForm extends Form
{
    protected $requestor;

    protected $approvers = [];

    protected $types = [];

    public function __construct(User $requestor, Request $model = null)
    {
        $this->requestor = $requestor;

        $requestor->departments->each(function (Department $department) {
            if ($department->head) {
                $this->approvers[$department->head->id] = $department->head->name;
            }
        });

        Department::where('is_global', 1)->orderBy('priority')->each(function (Department $department) {
            if ($department->head) {
                $this->approvers[$department->head->id] = $department->head->name;
            }    
        });

        $requestor->leaveBalances->each(function (LeaveBalance $balance) {
            if ($balance->leaveType) {
                $this->types[$balance->leaveType->id] = $balance->leaveType->name;
            }
        });

        parent::__construct($model);
    }

    public function create()
    {
        $this->add('requestor_id', Type\HiddenType::class, [
            'data' => $this->requestor->id
        ]);

        $this->add('requestor', Type\TextType::class, [
            'data'      => $this->requestor->name,

            'attr' => [
                'readonly'  => true
            ]
        ]);

        $this->add('approver_id', Type\ChoiceType::class, [
            'label'     => 'Approver',
            'choices'   => array_flip($this->approvers)
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
