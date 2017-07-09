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

use Auth;
use Password;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use App\Notifications\PasswordReset;
use App\Http\Controllers\Controller;
use App\Http\Forms\EditUserForm;
use App\Models\Department;
use App\Models\Group;
use App\Models\User;
use App\Components\Acl;

class UserController extends Controller
{
    public function __construct()
    {
        $this->can(Acl::ADMIN_USERS);
    }

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

        return view('admin.users.index', [
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
        $requests = $model->requests()->orderBy('id', 'DESC')->limit(20)->get();

        return view('admin.users.history', [
            'user'      => $model,
            'requests'  => $requests
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

        return view('admin.users.logs', [
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
                $model->picture = $data['picture'];
            }

            $model->save();

            $this->logAction('user_saved', [
                'name'  => $model->name
            ]);

            return redirect()->route('admin.users.index')
                ->with('message', ['success',
                    $editMode ? __('user.edited', ['name' => $model->name]) :
                        __('user.added', ['name' => $model->name])
                ]);
        }

        return view('admin.users.edit', [
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

        $this->logAction('user_deleted', [
            'name'  => $model->name
        ]);

        return redirect()->route('admin.users.index')
            ->with('message', ['success', __('user.deleted', ['name' => $model->name])]);
    }

    /**
     * User reset password action
     * 
     * @param \Illuminate\Http\Request $request Request object
     * @param \App\Models\User $model User model object
     * 
     * @return mixed
     */
    public function resetPassword(Request $request, User $model)
    {
        $model->password = null;
        $model->save();

        $token = Password::getRepository()->create($model);

        $model->notify(new PasswordReset($token));

        $this->logAction('request_password_reset', [
            'user'  => $model->name
        ]);

        return redirect()->route('admin.users.view', ['model' => $model])
            ->with('message', ['success', __('user.password_reset')]);
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
            return redirect()->route('admin.users.index')
                ->with('message', ['warning', __('user.not_found')]);
        }

        $model->restore();

        $this->logAction('user_restored', [
            'name'  => $model->name
        ]);

        return redirect()->route('admin.users.index')
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
            return redirect()->route('admin.users.index')
                ->with('message', ['warning', __('user.not_found')]);
        }

        $model->deletePicture();
        $model->forceDelete();

        $this->logAction('user_purged', [
            'name'  => $model->name
        ]);

        return redirect()->route('admin.users.deleted')
            ->with('message', ['success', __('user.purged', ['name' => $model->name])]);
    }
}
