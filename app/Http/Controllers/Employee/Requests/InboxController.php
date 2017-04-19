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
        $requests = Auth::user()->inbox()->with('requestor');
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

        return view('employee.requests.inbox.index', [
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
                    $model->disapprove($disapprovalReason);

                    Auth::user()->log('disapprove_request', [
                        'id' => $model->id,
                        'requestor' => $model->requestor->name
                    ]);

                    return redirect()->route('employee.requests.inbox.index')
                        ->with('message', ['success', __('request.disapproved')]);

                break;

                case 'escalate':
                    $approver = $model->escalate();

                    if ($approver) {
                        Auth::user()->log('escalate_request', [
                            'id' => $model->id,
                            'requestor' => $model->requestor->name,
                            'approver' => $approver->name
                        ]);

                        return redirect()->route('employee.requests.inbox.index')
                            ->with('message', ['success', __('request.escalated', 
                                ['name' => $approver->name]
                            )]);
                    }

                    return redirect()->route('employee.requests.inbox.index')
                        ->with('message', ['success', __('request.escalate_fail')]);

                break;

                case 'approve':
                    $status = $model->approve();

                    if ($status) {
                        Auth::user()->log('approve_request', [
                            'id' => $model->id,
                            'requestor' => $model->requestor->name
                        ]);

                        return redirect()->route('employee.requests.inbox.index')
                            ->with('message', ['success', __('request.approved')]);
                    }

                    return redirect()->route('employee.requests.inbox.index')
                        ->with('message', ['success', __('request.approve_fail')]);

                break;
            }
        }

        return redirect()->route('employee.requests.inbox.index');
    }
}
