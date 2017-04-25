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
use App\Models\Request;

class FilterRequestsForm extends Form
{
    public function create()
    {
        $types = [];

        foreach (config('request.types') as $key => $className) {
            $types[$className::getName()] = $key;
        }

        $this->add('status', Type\ChoiceType::class, [
            'choices'   => array_flip(Request::$statusLabels),
            'required'  => false
        ]);

        $this->add('requestor', Type\TextType::class, [
            'label' => 'Requestor Name',
            'required'  => false
        ]);

        $this->add('approver', Type\TextType::class, [
            'label' => 'Approver Name',
            'required'  => false
        ]);

        $this->add('type', Type\ChoiceType::class, [
            'choices'   => $types,
            'required'  => false
        ]);

        $this->add('date_filed_from', Type\DateType::class, [
            'label'     => 'Filed From',
            'required'  => false,
            'html5'     => true,
            'input'     => 'string',
            'widget'    => 'single_text'
        ]);

        $this->add('date_filed_to', Type\DateType::class, [
            'label'     => 'Filed To',
            'required'  => false,
            'html5'     => true,
            'input'     => 'string',
            'widget'    => 'single_text'
        ]);

        $this->add('date_requested_from', Type\DateType::class, [
            'label'     => 'Requested Date From',
            'required'  => false,
            'html5'     => true,
            'input'     => 'string',
            'widget'    => 'single_text'
        ]);

        $this->add('date_requested_to', Type\DateType::class, [
            'label'     => 'Requested Date To',
            'required'  => false,
            'html5'     => true,
            'input'     => 'string',
            'widget'    => 'single_text'
        ]);
    }
}
