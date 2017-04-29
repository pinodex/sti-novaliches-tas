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

use DB;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Forms\EditDepartmentForm;
use App\Http\Forms\DeleteDepartmentConfirmForm;
use App\Models\Department;
use App\Components\Acl;

class DepartmentController extends Controller
{
    public function __construct()
    {
        $this->can(Acl::ADMIN_DEPARTMENTS);
    }

    /**
     * Departments index page
     * 
     * @param \Illuminate\Http\Request $request Request object
     * 
     * @return mixed
     */
    public function index(Request $request)
    {
        $departments = Department::with('head');

        $showTrashed = $request->query->get('show') == 'deleted';
        $isAll = true;

        if ($showTrashed) {
            $departments->onlyTrashed();
            $isAll = false;
        }

        if ($searchName = $request->query->get('name')) {
            $departments->where('name', 'LIKE', '%' . $searchName . '%');
            $isAll = false;
        }

        return view('admin.departments.index', [
            'result'        => $departments->paginate(50),
            'departments'   => $departments,
            'trash'         => $showTrashed,
            'is_all'        => $isAll
        ]);
    }

    /**
     * Deleted departments page. Calls index route with show=delete query param
     * 
     * @param \Illuminate\Http\Request $request Request object
     * 
     * @return mixed
     */
    public function deleted(Request $request)
    {
        $request->query->set('show', 'deleted');

        return $this->index($request);
    }

    /**
     * Department edit page
     * 
     * @param \Illuminate\Http\Request $request Request object
     * @param \App\Models\Department $model Department model object
     * 
     * @return mixed
     */
    public function edit(Request $request, Department $model)
    {
        $editMode = $model->id !== null;

        $form = with(new EditDepartmentForm($model))
            ->getForm()
            ->handleRequest($request);

        if ($form->isValid()) {
            $data = $form->getData();

            // Set other department with the same head to unassigned
            Department::where('head_id', $data['head_id'])->where('id', '!=', $model->id)->update([
                'head_id' => null
            ]);

            $model->fill($data);
            $model->save();

            $this->logAction('department_saved', [
                'name'  => $model->name
            ]);

            return redirect()->route('admin.departments.index')
                ->with('message', ['success',
                    $editMode ? __('department.edited', ['name' => $model->name]) :
                        __('department.added', ['name' => $model->name])
                ]);
        }

        return view('admin.departments.edit', [
            'form'  => $form->createView(),
            'model' => $model
        ]);
    }

    /**
     * Department delete action
     * 
     * @param \Illuminate\Http\Request $request Request object
     * @param \App\Models\Department $model Department model object
     * 
     * @return mixed
     */
    public function delete(Request $request, Department $model)
    {
        if ($model->users()->count() > 0) {
            return redirect()->route('admin.departments.delete.confirm', ['model' => $model]);
        }

        $model->delete();

        $this->logAction('department_deleted', [
            'name'  => $model->name
        ]);

        return redirect()->route('admin.departments.index')
            ->with('message', ['success', __('department.deleted', ['name' => $model->name])]);
    }

    /**
     * Department delete confirmation page
     * 
     * @param \Illuminate\Http\Request $request Request object
     * @param \App\Models\Department $model Department model object
     * 
     * @return mixed
     */
    public function deleteConfirm(Request $request, Department $model)
    {
        $form = new DeleteDepartmentConfirmForm($model);

        $form = $form->getForm();
        $form->handleRequest($request);

        if ($form->isValid()) {
            $targetDep = $form['department']->getData();
            $targetDepModel = Department::find($targetDep);

            $model->users()->update([
                'department_id' => $targetDep
            ]);

            $model->delete();

            $this->logAction('department_users_moved', [
                'from'  => $model->name,
                'to'    => $targetDepModel ? $targetDepModel->name : 'Unassigned'
            ]);

            return redirect()->route('admin.departments.index')
                ->with('message', ['success', __('department.deleted', ['name' => $model->name])]);
        }

        return view('admin.departments.confirm', [
            'model' => $model,
            'form'  => $form->createView()
        ]);
    }

    /**
     * Department restore action
     * 
     * @param \Illuminate\Http\Request $request Request object
     *
     * @return mixed
     */
    public function restore(Request $request)
    {
        $id = $request->request->get('id');
        $model = Department::onlyTrashed()->find($id);

        if (!$model) {
            return redirect()->route('admin.departments.index')
                ->with('message', ['warning', __('department.not_found')]);
        }

        $model->restore();

        $this->logAction('department_restored', [
            'name'  => $model->name
        ]);

        return redirect()->route('admin.departments.index')
            ->with('message', ['success', __('department.restored', ['name' => $model->name])]);
    }

    /**
     * Department permanent delete action
     * 
     * @param \Illuminate\Http\Request $request Request object
     * 
     * @return mixed
     */
    public function purge(Request $request)
    {
        $id = $request->request->get('id');
        $model = Department::onlyTrashed()->find($id);

        if (!$model) {
            return redirect()->route('admin.departments.index')
                ->with('message', ['warning', __('department.not_found')]);
        }

        $model->forceDelete();

        $this->logAction('department_purged', [
            'name'  => $model->name
        ]);

        return redirect()->route('admin.departments.index')
            ->with('message', ['success', __('department.purged', ['name' => $model->name])]);
    }
}
