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
use App\Notifications\RequestReceived;
use App\Models\Request;

class OfficialBusinessType extends AbstractType
{
    public static function getName()
    {
        return 'Official Business';
    }

    public static function getMoniker()
    {
        return 'official_business';
    }

    public function getFormTemplate()
    {
        return '/templates/official_business.twig';
    }

    protected function onSubmitted(Form $form)
    {
        $data = $form->getData();

        $data['from_date'] = $this->getFormatted($data['from_date'], $data['from_time']);
        $data['to_date'] = $this->getFormatted($data['to_date'], $data['to_time']);

        $request = new Request();
        
        $request->fill($data);

        if ($this->getApprover()) {
            $request->approver_id = $this->getApprover()->id;
        }

        $request->type = $this->getMoniker();
        $request->incurred_balance = 0;

        $this->requestor->requests()->save($request);

        if ($this->getApprover()) {
            $this->getApprover()->notify(new RequestReceived($request));
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