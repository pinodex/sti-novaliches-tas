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

use DB;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Forms\EditDepartmentForm;
use App\Http\Forms\DeleteDepartmentConfirmForm;
use App\Models\Department;

class DepartmentsController extends Controller
{
    public function index(Request $request)
    {
        $departments = Department::with('head');

        $showTrashed = $request->query->get('show') == 'deleted';

        if ($showTrashed) {
            $departments->onlyTrashed();
        }

        if ($searchName = $request->query->get('name')) {
            $departments->where('name', 'LIKE', '%' . $searchName . '%');
        }

        return view('dashboard.departments.index', [
            'result'        => $departments->paginate(50),
            'departments'   => $departments,
            'trash'         => $showTrashed
        ]);
    }

    public function deleted(Request $request)
    {
        $request->query->set('show', 'deleted');

        return $this->index($request);
    }

    public function edit(Request $request, Department $model)
    {
        $editMode = $model->id !== null;

        $form = with(new EditDepartmentForm($model))
            ->getForm()
            ->handleRequest($request);

        if ($form->isValid()) {
            $data = $form->getData();

            $model->fill($data);
            $model->save();

            return redirect()->route('dashboard.departments.index')
                ->with('message', ['success',
                    $editMode ? __('group.edited', ['name' => $model->name]) :
                        __('group.added', ['name' => $model->name])
                ]);
        }

        return view('dashboard.departments.edit', [
            'form'  => $form->createView(),
            'model' => $model
        ]);
    }

    public function delete(Request $request, Department $model)
    {
        if ($model->users->count() > 0) {
            return redirect()->route('dashboard.departments.delete.confirm', ['model' => $model]);
        }

        $model->delete();

        return redirect()->route('dashboard.departments.index')
            ->with('message', ['success', __('group.deleted', ['name' => $model->name])]);
    }

    public function deleteConfirm(Request $request, Department $model)
    {
        $model->load('users');

        $form = new DeleteGroupConfirmForm($model);

        $form = $form->getForm();
        $form->handleRequest($request);

        if ($form->isValid()) {
            $action = $form['action']->getData();
            $targetGroup = $form['group']->getData();

            DB::transaction(function () use ($model, $action, $targetGroup) {
                switch ($action) {                    
                    case 'move':
                        $model->users()->getQuery()->update([
                            'group_id' => $targetGroup
                        ]);

                        break;

                    case 'delete':
                        $model->users()->getQuery()->delete();

                        break;
                }

                $model->delete();
            });

            return redirect()->route('dashboard.departments.index')
                ->with('message', ['success', __('group.deleted', ['name' => $model->name])]);
        }

        return view('dashboard.departments.confirm', [
            'model' => $model,
            'form'  => $form->createView()
        ]);
    }

    public function restore(Request $request)
    {
        $id = $request->request->get('id');
        $model = Department::onlyTrashed()->find($id);

        if (!$model) {
            return redirect()->route('dashboard.departments.index')
                ->with('message', ['warning', __('group.not_found')]);
        }

        $model->restore();

        return redirect()->route('dashboard.departments.index')
            ->with('message', ['success', __('group.restored', ['name' => $model->name])]);
    }

    public function purge(Request $request)
    {
        $id = $request->request->get('id');
        $model = Department::onlyTrashed()->find($id);

        if (!$model) {
            return redirect()->route('dashboard.departments.index')
                ->with('message', ['warning', __('group.not_found')]);
        }

        $model->forceDelete();

        return redirect()->route('dashboard.departments.index')
            ->with('message', ['success', __('group.purged', ['name' => $model->name])]);
    }
}
