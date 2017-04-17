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

class SickLeaveType extends AbstractType
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
        return 'Sick Leave';
    }

    public static function getMoniker()
    {
        return 'sick_leave';
    }

    public function getFormTemplate()
    {
        return '/templates/leave_form.twig';
    }

    protected function onSubmitted(Form $form)
    {
        $data = $form->getData();
        
        $days = $this->computeDays(
            $data['from_date'],
            $data['from_time'],
            $data['to_date'],
            $data['to_time']
        );

        if ($days == 0) {
            return null;
        }

        $data['from_date'] .= ' ' . array_flip($this->timeChoices)[$data['from_time']];
        $data['to_date'] .= ' ' . array_flip($this->timeChoices)[$data['to_time']];

        $data['from_date'] = (new DateTime($data['from_date']))->format('Y-m-d H:i:s');
        $data['to_date'] = (new DateTime($data['to_date']))->format('Y-m-d H:i:s');

        $request = new Request();
        
        $request->fill($data);

        if ($this->getApprover()) {
            $request->approver_id = $this->getApprover()->id;
        }

        $request->type = $this->getMoniker();
        $request->incurred_balance = $days;

        $this->requestor->requests()->save($request);

        return redirect()->route('employee.requests.index');
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
            'choices'       => $this->timeChoices
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
            'choices'       => $this->timeChoices
        ]);

        $this->form->add('reason', Type\TextareaType::class);
    }

    /**
     * Compute days inccured
     * 
     * @param string $fromDate Starting date in YYYY-MM-DD format
     * @param string $fromTime Starting time in 24H format
     * @param string $toDate Ending date in YYYY-MM-DD format
     * @param string $toTime Ending time in 24H format
     * 
     * @return int
     */
    protected function computeDays($fromDate, $fromTime, $toDate, $toTime)
    {
        $from = new DateTime($fromDate);
        $to = new DateTime($toDate);

        $fromTime = doubleval($fromTime);
        $toTime = doubleval($toTime);

        $days = intval($to->format('d')) - intval($from->format('d'));

        if ($toTime - $fromTime < 4) {
            $days += 0.5;
        }

        if ($toTime - $fromTime >= 4) {
            $days += 1;
        }

        return $days;
    }
}
