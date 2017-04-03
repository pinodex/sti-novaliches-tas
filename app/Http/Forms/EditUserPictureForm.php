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

class EditUserPictureForm extends Form
{
    public function create()
    {
        $this->add('image', Type\FileType::class, [
            'constraints'   => new Assert\Image(),
            'attr'          => [
                'accept'    => 'image/*'
            ]
        ]);
    }
}
