<?php

/*
 * This file is part of the TAS System for STI College Novaliches
 *
 * (c) Raphael Marco <raphaelmarco@outlook.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Http\Controllers\Admin;

use Auth;
use Illuminate\Http\Request;
use App\Models\Request as RequestModel;
use App\Http\Forms\FilterRequestsForm;
use App\Http\Forms\EditRequestForm;
use App\Http\Controllers\Controller;
use App\Components\Acl;

class RequestController extends Controller
{
    public function __construct()
    {
        $this->can(Acl::ADMIN_REQUESTS);
    }

    /**
     * Request inbox index page
     * 
     * @param \Illuminate\Http\Request $request Request object
     * 
     * @return mixed
     */
    public function index(Request $request)
    {
        $isFiltered = false;
        $requests = RequestModel::filter($request->query, $isFiltered)
            ->with('requestor', 'approver')
            ->orderBy('created_at', 'DESC');

        $form = with(new FilterRequestsForm)
            ->setData($request->query->all())
            ->getForm();

        return view('admin.requests.index', [
            'requests'  => $requests->paginate(50),
            'status'    => $request->query->get('status'),
            'form'      => $form->createView(),
            'is_all'    => $isFiltered == false
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
        $model->load('requestor', 'requestor.department', 'approver', 'approver.department');

        return view('admin.requests.view', [
            'model' => $model
        ]);
    }

    /**
     * Request action
     * 
     * @param \Illuminate\Http\Request $request Request object
     * @param \App\Models\Request $model Request model object
     * 
     * @return mixed
     */
    public function edit(Request $request, RequestModel $model)
    {
        $form = with(new EditRequestForm($model))
            ->getForm()
            ->handleRequest($request);

        if ($form->isValid()) {
            $data = $form->getData();

            $model->fill($data);
            $model->save();

            $this->logAction('request_edited', [
                'id'    => $model->id
            ]);

            return redirect()->route('admin.requests.index')
                ->with('message', ['success', __('request.saved')]);
        }

        return view('admin.requests.edit', [
            'model' => $model,
            'form'  => $form->createView()
        ]);
    }
}
