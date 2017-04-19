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

use DateTime;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\Extension\Core\Type;
use App\Models\Request;

class VacationLeaveType extends AbstractType
{
    protected $timeChoices = [
        '9:00 AM'   => '9',
        '9:30 AM'   => '9.5',
        '10:00 AM'  => '10',
        '10:30 AM'  => '10.5',
        '11:00 AM'  => '11',
        '11:30 AM'  => '11.5',
        '12:00 NN'  => '12',
        '12:30 PM'  => '12.5',
        '1:00 PM'   => '13',
        '1:30 PM'   => '13.5',
        '2:00 PM'   => '14',
        '2:30 PM'   => '14.5',
        '3:00 PM'   => '15',
        '3:30 PM'   => '15.5',
        '4:00 PM'   => '16',
        '4:30 PM'   => '16.5',
        '5:00 PM'   => '17'
    ];

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
        return '/templates/leave_form.twig';
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

        $this->form->add('reason', Type\TextareaType::class);
    }
}
