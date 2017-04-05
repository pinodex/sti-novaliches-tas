<?php

/*
 * This file is part of the TAS System for STI College Novaliches
 *
 * (c) Raphael Marco <raphaelmarco@outlook.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Http\Controllers\Dashboard;

use Auth;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Forms\CreateRequestForm;
use App\Extensions\Acl;
use App\Models\LeaveBalance;
use App\Models\Request as RequestModel;

class RequestsController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:submit_requests')->only('create');
    }

    /**
     * Requests index page
     * 
     * @return mixed
     */
    public function index()
    {
        $user = Auth::user();
         
        if (!Acl::for($user)->can(Acl::MANAGE_REQUESTS)) {
            return redirect()->route('dashboard.requests.me');
        }

        $requests = RequestModel::getForApprover($user);

        return view('dashboard.requests.index', [
            'requests'  => $requests
        ]);
    }

    /**
     * Current user submitted requests page
     * 
     * @return mixed
     */
    public function me()
    {
        $requests = Auth::user()->requests;

        return view('dashboard.requests.me', [
            'requests'  => $requests
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
        $model->load('requestor', 'approver', 'type');

        return view('dashboard.requests.view', [
            'model' => $model
        ]);
    }

    /**
     * Create request page
     * 
     * @param \Illuminate\Http\Request $request Request object
     * 
     * @return mixed
     */
    public function create(Request $request)
    {
        $user = Auth::user();

        $form = with(new CreateRequestForm($user))
            ->getForm()
            ->handleRequest($request);

        if ($form->isValid()) {
            $data = $form->getData();

            RequestModel::create($data);

            return redirect()->route('dashboard.requests.index')
                ->with('message', ['success', __('request.created')]);
        }

        return view('dashboard.requests.create', [
            'form'              => $form->createView()
        ]);
    }
}
