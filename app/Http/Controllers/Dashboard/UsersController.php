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
use App\Http\Forms\EditUserForm;
use App\Models\User;

class UsersController extends Controller
{
    protected $form;

    public function __construct()
    {
        $this->form = new EditUserForm();
    }

    public function index(Request $request)
    {
        $users = User::with('group');

        if ($searchName = $request->query->get('name')) {
            $users->where('name', 'LIKE', '%' . $searchName . '%');
        }

        return view('dashboard.users.index', [
            'result' => $users->paginate(50),
            'trash'  => false
        ]);
    }

    public function deleted(Request $request)
    {
        $users = User::with('group')->onlyTrashed();

        if ($searchName = $request->query->get('name')) {
            $users->where('name', 'LIKE', '%' . $searchName . '%');
        }

        return view('dashboard.users.index', [
            'result' => $users->paginate(50),
            'trash'  => true
        ]);
    }

    public function edit(Request $request, User $model = null)
    {
        $editMode = $model->id !== null;

        $this->form->setModel($model);

        $form = $this->form->getForm();
        $form->handleRequest($request);

        if ($form->isValid()) {
            $data = $form->getData();

            if (empty($data['password'])) {
                unset($data['password']);
            }

            $model->fill($data);
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
