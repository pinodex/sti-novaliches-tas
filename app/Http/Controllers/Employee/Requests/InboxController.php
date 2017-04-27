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
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\Request as RequestModel;
use App\Http\Forms\RequestInboxForm;
use App\Http\Controllers\Controller;
use App\Components\Acl;

class InboxController extends Controller
{
    public function __construct()
    {
        $this->can(Acl::APPROVE_DISAPPROVE_REQUESTS);
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
        $requests = Auth::user()->inbox()->orderBy('id', 'DESC')->with('requestor');
        $isAll = true;

        if ($show = $request->query->get('show')) {
            $isAll = false;

            if ($show == 'approved') {
                $requests->where('status', RequestModel::STATUS_APPROVED);
            }

            if ($show == 'escalated') {
                $requests->where('status', RequestModel::STATUS_ESCALATED);
            }

            if ($show == 'disapproved') {
                $requests->where('status', RequestModel::STATUS_DISAPPROVED);
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

        return view('employee.requests.inbox.view', [
            'model' => $model
        ]);
    }

    /**
     * Approve action
     * 
     * @param \Illuminate\Http\Request $request Request object
     * @param \App\Models\Request $model Request model object
     * 
     * @return mixed
     */
    public function approve(Request $request, RequestModel $model)
    {
        $approver = $model->approve();

        if ($approver instanceof User) {
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

        if ($approver) {
            Auth::user()->log('approve_request', [
                'id' => $model->id,
                'requestor' => $model->requestor->name
            ]);

            return redirect()->route('employee.requests.inbox.index')
                ->with('message', ['success', __('request.approved')]);
        }

        return redirect()->route('employee.requests.inbox.index')
            ->with('message', ['success', __('request.approve_fail')]);
    }

    /**
     * Disapprove action
     * 
     * @param \Illuminate\Http\Request $request Request object
     * @param \App\Models\Request $model Request model object
     * 
     * @return mixed
     */
    public function disapprove(Request $request, RequestModel $model)
    {
        $reason = $request->input('disapproval_reason');

        if (!$reason) {
            return redirect()->route('employee.requests.inbox.view', ['model' => $model])
                ->with('message', ['danger', __('request.disapprove_fail')]);
        }

        $model->disapprove($reason);

        Auth::user()->log('disapprove_request', [
            'id' => $model->id,
            'requestor' => $model->requestor->name
        ]);

        return redirect()->route('employee.requests.inbox.index')
            ->with('message', ['success', __('request.disapproved')]);
    }
}
