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

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Forms\EditLeaveTypeForm;
use App\Models\LeaveType;

class LeaveController extends Controller
{
    public function index()
    {
        $leaveTypes = LeaveType::all();

        return view('dashboard.leave.index', [
            'leave_types' => $leaveTypes
        ]);
    }

    public function typeEdit(Request $request, LeaveType $model)
    {
        $editMode = $model->id !== null;

        $form = with(new EditLeaveTypeForm($model))
            ->getForm()
            ->handleRequest($request);

        if ($form->isValid()) {
            $model->fill($form->getData());
            $model->save();

            return redirect()->route('dashboard.leave.index')
                ->with('message', ['success',
                    $editMode ? __('leave.edited', ['name' => $model->name]) :
                        __('leave.added', ['name' => $model->name])
                ]);
        }

        return view('dashboard.leave.type_edit', [
            'form'  => $form->createView()
        ]);
    }

    public function typeDelete(Request $request)
    {
        $id = $request->request->get('id');
        $model = LeaveType::find($id);

        if (!$model) {
            return redirect()->route('dashboard.leave.index')
                ->with('message', ['warning', __('leave.not_found')]);
        }

        $model->delete();

        return redirect()->route('dashboard.leave.index')
            ->with('message', ['success', __('leave.deleted', ['name' => $model->name])]);
    }
}
