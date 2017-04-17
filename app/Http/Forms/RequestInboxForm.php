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

class RequestInboxForm extends Form
{
    public function create()
    {
        $choices = [];

        $choices['disapprove'] = 'Disapprove Request';

        if ($this->model->approver && $this->model->approver->department) {
            if ($this->model->approver->department->head) {
                $choices['escalate'] = sprintf('Escalate to %s',
                    $this->model->approver->department->head->name
                );
            }
        }

        if ($this->model->approver && !$this->model->approver->department) {
            $choices['approve'] = 'Approve Request';
        }

        $this->add('action', Type\ChoiceType::class, [
            'choices' => array_flip($choices)
        ]);

        $this->add('disapproval_reason', Type\TextareaType::class, [
            'required' => false
        ]);
    }
}
