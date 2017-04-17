<?php

/*
 * This file is part of the TAS System for STI College Novaliches
 *
 * (c) Raphael Marco <raphaelmarco@outlook.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Components;

use Illuminate\Http\Request;
use App\Components\RequestType\AbstractType;
use App\Exceptions\RequestTypeNotFoundException;
use App\Models\Employee;

class RequestForm
{
    /**
     * @var \App\Components\RequestType\AbstractType
     */    
    protected $type;

    /**
     * @var \App\Models\Employee
     */
    protected $requestor;

    public function __construct(AbstractType $type, Employee $requestor)
    {
        $this->type = $type;
        $this->requestor = $requestor;
    }

    /**
     * Create instance with type from type name
     * 
     * @param string $typeName Type name
     * @param \App\Models\Employee $requestor Requestor employee model instance
     * 
     * @return \App\Components\RequestForm
     */
    public static function create($typeName, Employee $requestor)
    {
        $types = config('request.types');

        if (!array_key_exists($typeName, $types)) {
            throw new RequestTypeNotFoundException(sprintf('Request type %s not found', $typeName));
        }

        return new self(new $types[$typeName]($requestor), $requestor);
    }

    /**
     * Get type
     * 
     * @return \App\Components\RequestType\AbstractType
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Get request type form
     * 
     * @return \Symfony\Component\Form\Form
     */
    public function getForm()
    {
        return $this->type->getForm();
    }

    /**
     * Handle request for form
     * 
     * @param \Illuminate\Http\Request $request Request object
     */
    public function handleRequest(Request $request)
    {
        return $this->type->handleRequest($request);
    }
}
