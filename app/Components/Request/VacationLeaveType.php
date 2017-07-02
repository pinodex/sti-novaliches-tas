<?php

/*
 * This file is part of the TAS System for STI College Novaliches
 *
 * (c) Raphael Marco <raphaelmarco@outlook.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Components\Request;

use DateTime;
use Illuminate\Http\Request;
use Symfony\Component\Form\Extension\Core\Type;
use App\Notifications\RequestReceived;
use App\Models\Request as RequestModel;

class VacationLeaveType extends AbstractType
{
    public static function getName()
    {
        return 'Vacation Leave';
    }

    public static function getMoniker()
    {
        return 'vacation_leave';
    }

    public function getFormTemplate()
    {
        return '/templates/leave.twig';
    }

    protected function onSubmit(Request $request)
    {
        $model = $this->makeModel($request);

        if ($model->incurred_balance > $this->requestor->leaves_balance) {
            return redirect()->route('employee.requests.index')
                ->with('message', ['danger', __('request.insufficient')]);
        }

        $this->requestor->requests()->save($model);

        if ($this->getApprover()) {
            $this->getApprover()->notify(new RequestReceived($model));
        }

        return redirect()->route('employee.requests.index')
            ->with('message', ['success', __('request.submitted')]);
    }

    protected function buildForm()
    {
        $approver = $this->getApprover();

        $this->form->add('_requestor', Type\TextType::class, [
            'label' => 'Requestor',
            'data'  => $this->requestor->name,
            'attr'  => [
                'readonly'  => true
            ]
        ]);

        $this->form->add('_approver', Type\TextType::class, [
            'label' => 'Approver',
            'data'  => $approver ? $approver->name : 'Unknown',
            'attr'  => [
                'readonly'  => true
            ]
        ]);

        $this->form->add('start_date', Type\DateType::class, [
            'html5'         => true,
            'input'         => 'string',
            'widget'        => 'single_text',

            'attr'          => [
                'min'   => date('Y-m-d')
            ]
        ]);

        $this->form->add('start_time', Type\ChoiceType::class, [
            'choices'       => $this->timeChoices
        ]);

        $this->form->add('end_date', Type\DateType::class, [
            'html5'         => true,
            'input'         => 'string',
            'widget'        => 'single_text',

            'attr'          => [
                'min'   => date('Y-m-d')
            ]
        ]);

        $this->form->add('end_time', Type\ChoiceType::class, [
            'choices'       => $this->timeChoices
        ]);

        $this->form->add('subtype', Type\ChoiceType::class, [
            'label'         => 'Type',
            'choices'       => array_flip(RequestModel::$typeLabels)
        ]);

        $this->form->add('reason', Type\TextareaType::class);
    }
}
