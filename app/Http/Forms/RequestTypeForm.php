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

class RequestTypeForm extends Form
{
    public function create()
    {
        $types = [];

        foreach (config('request.types') as $key => $className) {
            $types[$className::getName()] = $key;
        }

        $this->add('type', Type\ChoiceType::class, [
            'choices'   => $types
        ]);
    }
}
