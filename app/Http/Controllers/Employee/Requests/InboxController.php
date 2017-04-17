<?php

/*
 * This file is part of the TAS System for STI College Novaliches
 *
 * (c) Raphael Marco <raphaelmarco@outlook.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Http\Controllers\Employee\Requests;

use Auth;
use Illuminate\Http\Request;
use App\Models\Request as RequestModel;
use App\Http\Forms\RequestInboxForm;
use App\Http\Controllers\Controller;

class InboxController extends Controller
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
        $requests = Auth::user()->inbox()->with('requestor')->paginate(50);

        return view('employee.requests.inbox.index', [
            'requests'  => $requests,
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
        $form = with(new RequestInboxForm($model))->getForm();

        return view('employee.requests.inbox.view', [
            'model' => $model,
            'form'  => $form->createView()
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
    public function action(Request $request, RequestModel $model)
    {
        $form = with(new RequestInboxForm($model))
            ->getForm()
            ->handleRequest($request);

        if ($form->isValid()) {
            $action = $form['action']->getData();
            $disapprovalReason = $form['disapproval_reason']->getData();

            switch ($action) {
                case 'disapprove':
                    $model->is_approved = false;
                    $model->disapproval_reason = $disapprovalReason;
                    $model->save();

                    return redirect()->route('employee.requests.inbox.index')
                        ->with('message', ['success', __('request.disapproved')]);

                break;

                case 'escalate':
                    if ($model->approver && $model->approver->department) {
                        if ($model->approver->department->head) {
                            $model->approver_id = $model->approver->department->head->id;
                            $model->is_approved = 5;

                            $model->save();

                            return redirect()->route('employee.requests.inbox.index')
                                ->with('message', ['success', __('request.escalated', 
                                    ['name' => $model->approver->department->head->name]
                                )]);
                        }
                    }

                break;

                case 'approve':
                    if ($model->approver && !$model->approver->department) {
                        $model->is_approved = true;

                        $model->save();

                        return redirect()->route('employee.requests.inbox.index')
                            ->with('message', ['success', __('request.approved')]);
                    }

                break;
            }
        }

        return redirect()->route('employee.requests.inbox.index');
    }
}
