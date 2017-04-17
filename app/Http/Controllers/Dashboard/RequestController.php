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
use App\Models\Request as RequestModel;
use App\Http\Forms\EditRequestForm;
use App\Http\Controllers\Controller;

class RequestController extends Controller
{
    /**
     * Request inbox index page
     * 
     * @param \Illuminate\Http\Request $request Request object
     * 
     * @return mixed
     */
    public function index(Request $request)
    {
        $requests = RequestModel::with('requestor', 'approver');
        $isAll = true;

        if ($show = $request->query->get('show')) {
            $isAll = false;

            if ($show == 'approved') {
                $requests->where('is_approved', 1);
            }

            if ($show == 'escalated') {
                $requests->where('is_approved', 5);
            }

            if ($show == 'disapproved') {
                $requests->where('is_approved', 0);
            }
        }

        return view('dashboard.requests.index', [
            'requests'  => $requests->paginate(50),
            'is_all'    => $isAll,
            'show'      => $show
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

        return view('dashboard.requests.view', [
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

            if ($data['is_approved'] == 'null') {
                $data['is_approved'] = null;
            }

            $model->fill($data);
            $model->save();

            return redirect()->route('dashboard.requests.index')
                ->with('message', ['success', __('request.saved')]);
        }

        return view('dashboard.requests.edit', [
            'model' => $model,
            'form'  => $form->createView()
        ]);
    }
}
