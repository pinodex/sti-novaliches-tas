<?php

/*
 * This file is part of the TAS System for STI College Novaliches
 *
 * (c) Raphael Marco <raphaelmarco@outlook.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Http\Controllers\Employee;

use Auth;
use Illuminate\Http\Request;
use App\Components\RequestForm;
use App\Models\Request as RequestModel;
use App\Http\Controllers\Controller;
use App\Http\Forms\RequestTypeForm;
use App\Exceptions\RequestTypeNotFoundException;

class RequestController extends Controller
{
    /**
     * Requests index page
     * 
     * @param \Illuminate\Http\Request $request Request object
     * 
     * @return mixed
     */
    public function index(Request $request)
    {
        $requests = Auth::user()->requests()->orderBy('id', 'DESC')->with('approver')->paginate(50);

        $form = with(new RequestTypeForm)->getForm();

        if ($form->isValid()) {
            $data = $form->getData();
        }

        return view('employee.requests.index', [
            'requests'  => $requests,
            'form'      => $form->createView()
        ]);
    }

    /**
     * Request view page
     * 
     * @param \Illuminate\Http\Request $request Request object
     * @param \App\Models\Request $model Request model object
     * 
     * @return mixed
     */
    public function view(Request $request, RequestModel $model)
    {
        return view('employee.requests.view', [
            'model' => $model
        ]);
    }

    /**
     * Create request action
     * 
     * @param \Illuminate\Http\Request $request Request object
     * 
     * @return mixed
     */
    public function create(Request $request)
    {
        $type = $request->get('type');

        return redirect()->route('employee.requests.create.type', [
            'type' => $type
        ]);
    }

    /**
     * Create request page
     * 
     * @param \Illuminate\Http\Request $request Request object
     * 
     * @return mixed
     */
    public function createType(Request $request, $typeName)
    {
        $user = Auth::user();

        try {
            $requestForm = RequestForm::create($typeName, $user);
        } catch (RequestTypeNotFoundException $e) {
            abort(404);
        }

        $form = $requestForm->getForm();
        $response = $requestForm->handleRequest($request);

        if ($response) {
            return $response;
        }

        return view('employee.requests.create', [
            'request_form'  => $requestForm,
            'form'          => $form->createView(),
        ]);
    }
}
