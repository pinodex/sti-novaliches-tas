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
use App\Models\User;
use App\Models\Group;

class UsersController extends Controller
{
    public function index(Request $request)
    {
        $users = User::with('group');
        $groups = Group::all();

        $showTrashed = $request->query->get('show') == 'deleted';

        if ($showTrashed) {
            $users->onlyTrashed();
        }

        if ($searchName = $request->query->get('name')) {
            $users->where('name', 'LIKE', '%' . $searchName . '%');
        }

        if ($searchGroup = $request->query->get('group')) {
            $users->where(function (Builder $query) use ($searchGroup) {
                if ($searchGroup == 'unassigned') {
                    $query->whereIn('group_id', [null, 0]);

                    return;
                }

                if ($searchGroup != 'all') {
                    $query->where('group_id', $searchGroup);
                }
            });
        }

        return view('dashboard.users.index', [
            'result' => $users->paginate(50),
            'groups' => $groups,
            'trash'  => $showTrashed
        ]);
    }

    public function deleted(Request $request)
    {
        $request->query->set('show', 'deleted');

        return $this->index($request);
    }

    public function view(Request $request, User $model)
    {
        $model->load(['group', 'department', 'departments', 'profile']);

        return view('dashboard.users.view', [
            'user'  => $model
        ]);
    }

    public function edit(Request $request, User $model)
    {
        $editMode = $model->id !== null;

        $model->load('departments');

        $form = with(new EditUserForm($model))
            ->getForm()
            ->handleRequest($request);

        if ($form->isValid()) {
            $data = $form->getData();

            if (empty($data['password'])) {
                unset($data['password']);
            }

            $model->fill($data);
            $model->departments()->sync($data['departments']);

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

    public function delete(Request $request, User $model)
    {
        $model->delete();

        return redirect()->route('dashboard.users.index')
            ->with('message', ['success', __('user.deleted', ['name' => $model->name])]);
    }

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

    public function purge(Request $request)
    {
        $id = $request->request->get('id');
        $model = User::onlyTrashed()->find($id);

        if (!$model) {
            return redirect()->route('dashboard.users.index')
                ->with('message', ['warning', __('user.not_found')]);
        }

        $model->forceDelete();

        return redirect()->route('dashboard.users.index')
            ->with('message', ['success', __('user.purged', ['name' => $model->name])]);
    }
}
