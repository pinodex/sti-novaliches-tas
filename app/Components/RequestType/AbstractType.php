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

use Symfony\Component\Form\Form as SymfonyForm;
use Illuminate\Http\Request;
use App\Http\Forms\Form;
use App\Models\Employee;

abstract class AbstractType
{
    /**
     * @var \App\Http\Forms\Form
     */
    protected $form;

    /**
     * @var \App\Models\Employee
     */
    protected $requestor;

    /**
     * @param \App\Models\Employee $requestor Requestor employee model instance
     */
    public function __construct(Employee $requestor)
    {
        $this->form = new Form();
        $this->requestor = $requestor;

        $this->buildForm();
        $this->form = $this->form->getForm();
    }

    /**
     * Get form
     * 
     * @return \App\Http\Forms\Form
     */
    public function getForm()
    {
        return $this->form;
    }

    /**
     * Get form template
     * 
     * @return string
     */
    abstract public function getFormTemplate();

    public function handleRequest(Request $request)
    {
        $this->form->handleRequest($request);

        if ($request->method() == 'POST') {
            return $this->onSubmitted($this->form);
        }
    }

    /**
     * Get approver for requestor
     * 
     * @return \App\Models\Employee
     */
    protected function getApprover()
    {
        if ($this->requestor->department && $this->requestor->department->head) {
            if ($this->requestor->id != $this->requestor->department->head->id) {
                return $this->requestor->department->head;
            }
        }   
    }

    /**
     * Get type name
     * 
     * @return string
     */
    abstract public static function getName();

    /**
     * Get type moniker
     * 
     * @return string
     */
    abstract public static function getMoniker();

    /**
     * Build the form by adding fields to it
     */
    abstract protected function buildForm();

    /**
     * Called on form post request
     * 
     * @return mixed
     */
    abstract protected function onSubmitted(SymfonyForm $form);
}
