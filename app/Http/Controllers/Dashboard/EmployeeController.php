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
use Illuminate\Database\Eloquent\Builder;
use App\Http\Controllers\Controller;
use App\Http\Forms\EditEmployeeForm;
use App\Models\Department;
use App\Models\Employee;

class EmployeeController extends Controller
{
    /**
     * Employee index page
     * 
     * @param \Illuminate\Http\Request $request Request object
     * 
     * @return mixed
     */
    public function index(Request $request)
    {
        $result = Employee::with('department');
        $departments = Department::all();

        $showTrashed = $request->query->get('show') == 'deleted';
        $isAll = true;

        if ($showTrashed) {
            $result->onlyTrashed();
            $isAll = false;
        }

        if ($name = $request->query->get('name')) {
            Employee::searchName($result, $name);
            $isAll = false;
        }

        if ($department = $request->query->get('department')) {
            $result->where(function (Builder $query) use ($department, &$isAll) {
                if ($department == 'unassigned') {
                    $query->whereIn('department_id', [null, 0]);
                    $isAll = false;

                    return;
                }

                if ($department != 'all') {
                    $query->where('department_id', $department);
                    $isAll = false;

                    return;
                }
            });
        }

        return view('dashboard.employees.index', [
            'result'        => $result->paginate(50),
            'departments'   => $departments,
            'trash'         => $showTrashed,
            'is_all'        => $isAll
        ]);
    }

    /**
     * Deleted users page. Calls index route with show=delete query param
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
     * Employee view page
     * 
     * @param \Illuminate\Http\Request $request Request object
     * @param \App\Models\Employee $model Employee model object
     * 
     * @return mixed
     */
    public function view(Request $request, Employee $model)
    {
        return view('dashboard.employees.history', [
            'user'  => $model
        ]);
    }

    /**
     * Employee logs page
     * 
     * @param \Illuminate\Http\Request $request Request object
     * @param \App\Models\Employee $model Employee model object
     * 
     * @return mixed
     */
    public function logs(Request $request, Employee $model)
    {
        $logs = $model->logs()->paginate(50);

        return view('dashboard.employees.logs', [
            'user'  => $model,
            'logs'  => $logs
        ]);
    }

    /**
     * Employee edit page
     * 
     * @param \Illuminate\Http\Request $request Request object
     * @param \App\Models\Employee $model Employee model object
     * 
     * @return mixed
     */
    public function edit(Request $request, Employee $model)
    {
        $editMode = $model->id !== null;

        $form = with(new EditEmployeeForm($model))
            ->getForm()
            ->handleRequest($request);

        if ($form->isValid()) {
            $data = $form->getData();

            if (empty($data['password'])) {
                unset($data['password']);
            }

            $model->fill($data);
            
            if ($data['picture']) {
                try {
                    $model->picture = $data['picture'];
                } catch (\Exception $e) {}
            }

            if ($data['require_password_change']) {
                $model->last_password_change_at = null;
            }

            $model->save();

            return redirect()->route('dashboard.employees.index')
                ->with('message', ['success',
                    $editMode ? __('employee.edited', ['name' => $model->name]) :
                        __('employee.added', ['name' => $model->name])
                ]);
        }

        return view('dashboard.employees.edit', [
            'form'  => $form->createView(),
            'model' => $model
        ]);
    }

    /**
     * Employee delete action
     * 
     * @param \Illuminate\Http\Request $request Request object
     * @param \App\Models\Employee $model Employee model object
     * 
     * @return mixed
     */
    public function delete(Request $request, Employee $model)
    {
        $model->delete();

        return redirect()->route('dashboard.employees.index')
            ->with('message', ['success', __('employee.deleted', ['name' => $model->name])]);
    }

    /**
     * Employee restore action
     * 
     * @param \Illuminate\Http\Request $request Request object
     * 
     * @return mixed
     */
    public function restore(Request $request)
    {
        $id = $request->request->get('id');
        $model = Employee::onlyTrashed()->find($id);

        if (!$model) {
            return redirect()->route('dashboard.employees.index')
                ->with('message', ['warning', __('employee.not_found')]);
        }

        $model->restore();

        return redirect()->route('dashboard.employees.index')
            ->with('message', ['success', __('employee.restored', ['name' => $model->name])]);
    }

    /**
     * Employee permanently delete action
     * 
     * @param \Illuminate\Http\Request $request Request object
     * 
     * @return mixed
     */
    public function purge(Request $request)
    {
        $id = $request->request->get('id');
        $model = Employee::onlyTrashed()->find($id);

        if (!$model) {
            return redirect()->route('dashboard.employees.index')
                ->with('message', ['warning', __('employee.not_found')]);
        }

        $model->deletePicture();
        $model->forceDelete();

        return redirect()->route('dashboard.employees.deleted')
            ->with('message', ['success', __('employee.purged', ['name' => $model->name])]);
    }
}
