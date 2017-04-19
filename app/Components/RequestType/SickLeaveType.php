<?php

/*
 * This file is part of the TAS System for STI College Novaliches
 *
 * (c) Raphael Marco <raphaelmarco@outlook.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Components\RequestType;

use Symfony\Component\Form\Form;
use Symfony\Component\Form\Extension\Core\Type;
use App\Models\Request;

class SickLeaveType extends AbstractType
{
    protected $reasons = [
        'Headache, toothache, heartache',
        'Another reason',
        'Another reason 2',
    ];

    public static function getName()
    {
        return 'Sick Leave';
    }

    public static function getMoniker()
    {
        return 'sick_leave';
    }

    public function getFormTemplate()
    {
        return '/templates/sick_leave.twig';
    }

    protected function onSubmitted(Form $form)
    {
        $data = $form->getData();
        
        $days = $this->computeDays(
            $this->getFormatted($data['from_date'], $data['from_time']),
            $this->getFormatted($data['to_date'], $data['to_time'])
        );

        if ($days == 0) {
            return null;
        }

        if ($days > $this->requestor->leaves_balance) {
            return redirect()->route('employee.requests.index')
                ->with('message', ['danger', __('request.insufficient')]);
        }

        $data['from_date'] = $this->getFormatted($data['from_date'], $data['from_time']);
        $data['to_date'] = $this->getFormatted($data['to_date'], $data['to_time']);

        $request = new Request();

        if ($data['reason'] = 'other') {
            $data['reason'] = $data['_custom_reason'];
        }
        
        $request->fill($data);

        if ($this->getApprover()) {
            $request->approver_id = $this->getApprover()->id;
        }

        $request->type = $this->getMoniker();
        $request->incurred_balance = $days;

        $this->requestor->requests()->save($request);

        return redirect()->route('employee.requests.index')
            ->with('message', ['success', __('request.submitted')]);
    }

    protected function buildForm()
    {
        $approver = $this->getApprover();
        $reasons = array_combine($this->reasons, $this->reasons);

        $reasons['Other (please specify)'] = 'other';

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

        $this->form->add('from_date', Type\DateType::class, [
            'required'      => false,
            'html5'         => true,
            'input'         => 'string',
            'widget'        => 'single_text',

            'attr'          => [
                'min'   => date('Y-m-d')
            ]
        ]);

        $this->form->add('from_time', Type\ChoiceType::class, [
            'choices'       => array_combine($this->timeChoices, $this->timeChoices)
        ]);

        $this->form->add('to_date', Type\DateType::class, [
            'required'      => false,
            'html5'         => true,
            'input'         => 'string',
            'widget'        => 'single_text',

            'attr'          => [
                'min'   => date('Y-m-d')
            ]
        ]);

        $this->form->add('to_time', Type\ChoiceType::class, [
            'choices'       => array_combine($this->timeChoices, $this->timeChoices)
        ]);

        $this->form->add('reason', Type\ChoiceType::class, [
            'choices'   => $reasons
        ]);

        $this->form->add('_custom_reason', Type\TextareaType::class, [
            'label'     => 'Reason',
            'attr'      => [
                'placeholder' => 'Please specify the reason for your sick leave request.'
            ],
            'required'  => false
        ]);
    }
}
