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

class UndertimeType extends AbstractType
{
    protected $hourChoices = [
        '0.5',
        '1.0',
        '1.5',
        '2.0'
    ];

    public static function getName()
    {
        return 'Undertime';
    }

    public static function getMoniker()
    {
        return 'undertime';
    }

    public function getFormTemplate()
    {
        return '/templates/undertime.twig';
    }

    protected function onSubmitted(Form $form)
    {
        $data = $form->getData();

        $data['from_date'] = date('Y-m-d H:i:s');
        $data['to_date'] = date('Y-m-d H:i:s');

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

        $this->form->add('hours', Type\ChoiceType::class, [
            'choices'       => array_combine($this->hourChoices, $this->hourChoices)
        ]);

        $this->form->add('reason', Type\TextareaType::class);
    }
}
