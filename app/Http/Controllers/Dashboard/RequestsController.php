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

    public function me()
    {
        $requests = Auth::user()->requests;
        $requests->load('approver', 'type');

        return view('dashboard.requests.me', [
            'requests'  => $requests
        ]);
    }

    public function view(Request $request, RequestModel $model)
    {
        $model->load('requestor', 'approver', 'type');

        return view('dashboard.requests.view', [
            'model' => $model
        ]);
    }

    public function create(Request $request)
    {
        $user = Auth::user();

        $user->load('departments', 'departments.head', 'leaveBalances', 'leaveBalances.leaveType');

        $form = with(new CreateRequestForm($user))
            ->getForm()
            ->handleRequest($request);

        if ($form->isValid()) {
            $data = $form->getData();

            $data['days'] = 1;

            RequestModel::create($data);

            return redirect()->route('dashboard.requests.index')
                ->with('message', ['success', __('request.created')]);
        }

        $balances = [];

        $user->leaveBalances->each(function (LeaveBalance $balance) use (&$balances) {
            if ($balance->leaveType) {
                $balances[$balance->leaveType->id] = $balance->entitlement;
            }
        });

        return view('dashboard.requests.create', [
            'form'              => $form->createView(),
            'requestBalances'   => $balances
        ]);
    }
}
