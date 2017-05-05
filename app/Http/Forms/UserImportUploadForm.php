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
use App\Models\Department;
use App\Models\Profile;
use App\Models\Group;

class UserImportUploadForm extends Form
{
    public function create()
    {
        $departments = Department::all();
        $profiles = Profile::all();
        $groups = Group::all();

        $this->add('file', Type\FileType::class, [
            'constraints' => new Assert\File([
                'mimeTypes' => ['vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']
            ]),

            'attr' => [
                'accept' => 'vnd.ms-excel, .xlsx'
            ]
        ]);

        $this->add('default_department', Type\ChoiceType::class, [
            'choices'   => $this->toChoices($departments, true)
        ]);

        $this->add('default_group', Type\ChoiceType::class, [
            'choices'   => $this->toChoices($groups, true)
        ]);

        $this->add('default_profile', Type\ChoiceType::class, [
            'choices'   => $this->toChoices($profiles, true)
        ]);

        $this->add('default_password', Type\TextType::class);
    }
}
