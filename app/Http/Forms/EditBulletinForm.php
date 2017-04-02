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
use Illuminate\Support\Collection;
use App\Models\Group;

class EditBulletinForm extends Form
{
    public function create()
    {
    	$groups = Group::all();

        $this->add('title', Type\TextType::class, [
            'attr'  => [
                'autofocus' => true
            ],

            'required' => false
        ]);

        $this->add('contents', Type\TextareaType::class);
    }
}
