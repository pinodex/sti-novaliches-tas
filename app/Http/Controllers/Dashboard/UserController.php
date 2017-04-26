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
use App\Http\Forms\EditUserForm;
use App\Models\Department;
use App\Models\Group;
use App\Models\User;

class UserController extends Controller
{
    /**
     * User index page
     * 
     * @param \Illuminate\Http\Request $request Request object
     * 
     * @return mixed
     */
    public function index(Request $request)
    {
        $result = User::with('department', 'group');
        $departments = Department::all();
        $groups = Group::all();

        $showTrashed = $request->query->get('show') == 'deleted';
        $isAll = true;

        if ($showTrashed) {
            $result->onlyTrashed();
            $isAll = false;
        }

        if ($name = $request->query->get('name')) {
            User::searchName($result, $name);
            $isAll = false;
        }

        $result->when($request->query->get('department'), function ($builder) use ($request, &$isAll) {
            $isAll = false;
            $department = $request->query->get('department');

            $builder->where(function (Builder $query) use ($department) {
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
        });

        $result->when($request->query->get('group'), function ($builder) use ($request, &$isAll) {
            $isAll = false;
            $group = $request->query->get('group');

            $builder->where(function (Builder $query) use ($group) {
                if ($group == 'unassigned') {
                    $query->whereIn('group_id', [null, 0]);
                    $isAll = false;

                    return;
                }

                if ($group != 'all') {
                    $query->where('group_id', $group);
                    $isAll = false;

                    return;
                }
            });
        });

        return view('dashboard.users.index', [
            'result'        => $result->paginate(50),
            'departments'   => $departments,
            'groups'        => $groups,
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
     * User view page
     * 
     * @param \Illuminate\Http\Request $request Request object
     * @param \App\Models\User $model User model object
     * 
     * @return mixed
     */
    public function view(Request $request, User $model)
    {
        return view('dashboard.users.history', [
            'user'  => $model
        ]);
    }

    /**
     * User logs page
     * 
     * @param \Illuminate\Http\Request $request Request object
     * @param \App\Models\User $model User model object
     * 
     * @return mixed
     */
    public function logs(Request $request, User $model)
    {
        $logs = $model->logs()->orderBy('id', 'DESC')->paginate(50);

        return view('dashboard.users.logs', [
            'user'  => $model,
            'logs'  => $logs
        ]);
    }

    /**
     * User edit page
     * 
     * @param \Illuminate\Http\Request $request Request object
     * @param \App\Models\User $model User model object
     * 
     * @return mixed
     */
    public function edit(Request $request, User $model)
    {
        $editMode = $model->id !== null;

        $form = with(new EditUserForm($model))
            ->getForm()
            ->handleRequest($request);

        if ($form->isValid()) {
            $data = $form->getData();

            if (empty($data['password'])) {
                unset($data['password']);
            }

            unset($data['name']);
            $model->fill($data);
            
            if ($data['picture']) {
                try {
                    $model->picture = $data['picture'];
                } catch (\Exception $e) {}
            }

            $model->save();

            return redirect()->route('dashboard.users.index')
                ->with('message', ['success',
                    $editMode ? __('user.edited', ['name' => $model->name]) :
                        __('user.added', ['name' => $model->name])
                ]);
        }

        return view('dashboard.users.edit', [
            'form'  => $form->createView(),
            'model' => $model
        ]);
    }

    /**
     * User delete action
     * 
     * @param \Illuminate\Http\Request $request Request object
     * @param \App\Models\User $model User model object
     * 
     * @return mixed
     */
    public function delete(Request $request, User $model)
    {
        $model->delete();

        return redirect()->route('dashboard.users.index')
            ->with('message', ['success', __('user.deleted', ['name' => $model->name])]);
    }

    /**
     * User restore action
     * 
     * @param \Illuminate\Http\Request $request Request object
     * 
     * @return mixed
     */
    public function restore(Request $request)
    {
        $id = $request->request->get('id');
        $model = User::onlyTrashed()->find($id);

        if (!$model) {
            return redirect()->route('dashboard.users.index')
                ->with('message', ['warning', __('user.not_found')]);
        }

        $model->restore();

        return redirect()->route('dashboard.users.index')
            ->with('message', ['success', __('user.restored', ['name' => $model->name])]);
    }

    /**
     * User permanently delete action
     * 
     * @param \Illuminate\Http\Request $request Request object
     * 
     * @return mixed
     */
    public function purge(Request $request)
    {
        $id = $request->request->get('id');
        $model = User::onlyTrashed()->find($id);

        if (!$model) {
            return redirect()->route('dashboard.users.index')
                ->with('message', ['warning', __('user.not_found')]);
        }

        $model->deletePicture();
        $model->forceDelete();

        return redirect()->route('dashboard.users.deleted')
            ->with('message', ['success', __('user.purged', ['name' => $model->name])]);
    }
}