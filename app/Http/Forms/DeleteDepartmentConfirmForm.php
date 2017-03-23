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
use App\Models\Department;

class DeleteDepartmentConfirmForm extends Form
{
	protected $department;

	public function __construct(Department $department)
	{
		$this->department = $department;

		parent::__construct();
	}

    public function create()
    {
    	$departments = Department::where('id', '!=', $this->department->id)->get();

    	$this->add('department', Type\ChoiceType::class, [
    		'label'		=> ' ',
    		'choices'	=> $this->toChoices($departments, true)
    	]);
    }
}
