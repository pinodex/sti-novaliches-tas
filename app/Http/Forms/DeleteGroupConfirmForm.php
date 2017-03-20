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
use App\Models\Group;

class DeleteGroupConfirmForm extends Form
{
	protected $group;

	public function __construct(Group $group)
	{
		$this->group = $group;

		parent::__construct();
	}

    public function create()
    {
    	$groups = Group::where('id', '!=', $this->group->id)->get();

    	$this->add('action', Type\ChoiceType::class, [
    		'label' 	=> ' ',
    		
    		'attr'		=> [
    			'v-model' => 'groupDeleteAction'
    		],

    		'choices' 	=> [
    			'Move to other group' => 'move',
    			'Delete users' => 'delete'
    		]
    	]);

    	$this->add('group', Type\ChoiceType::class, [
    		'label'		=> 'Target group',
    		'choices'	=> $this->toChoices($groups, true),
    		'attr'		=> [
    			':disabled' => 'groupDeleteAction != "move"',
    		]
    	]);
    }
}
