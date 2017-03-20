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
use App\Http\Forms\EditGroupForm;
use App\Http\Forms\DeleteGroupConfirmForm;
use App\Models\Group;

class GroupsController extends Controller
{
    protected $form;

    public function __construct()
    {
        $this->form = new EditGroupForm();
    }

    public function index(Request $request)
    {
        $groups = Group::with('users');

        $showTrashed = $request->query->get('show') == 'deleted';

        if ($showTrashed) {
            $groups->onlyTrashed();
        }

        if ($searchName = $request->query->get('name')) {
            $groups->where('name', 'LIKE', '%' . $searchName . '%');
        }

        return view('dashboard.groups.index', [
            'result' => $groups->paginate(50),
            'groups' => $groups,
            'trash'  => $showTrashed
        ]);
    }

    public function deleted(Request $request)
    {
        $request->query->set('show', 'deleted');

        return $this->index($request);
    }

    public function edit(Request $request, Group $model = null)
    {
        $model->load('users');

        $editMode = $model->id !== null;

        $this->form->setModel($model);

        $form = $this->form->getForm();
        $form->handleRequest($request);

        if ($form->isValid()) {
            $data = $form->getData();

            $model->fill($data);
            $model->save();

            return redirect()->route('dashboard.groups.index')
                ->with('message', ['success',
                    $editMode ? __('group.edited', ['name' => $model->name]) :
                        __('group.added', ['name' => $model->name])
                ]);
        }

        return view('dashboard.groups.edit', [
            'form'  => $form->createView(),
            'model' => $model
        ]);
    }

    public function delete(Request $request, Group $model)
    {
        if ($model->users->count() > 0) {
            return redirect()->route('dashboard.groups.delete.confirm', ['model' => $model]);
        }

        $model->delete();

        return redirect()->route('dashboard.groups.index')
            ->with('message', ['success', __('group.deleted', ['name' => $model->name])]);
    }

    public function deleteConfirm(Request $request, Group $model)
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

            return redirect()->route('dashboard.groups.index')
                ->with('message', ['success', __('group.deleted', ['name' => $model->name])]);
        }

        return view('dashboard.groups.confirm', [
            'model' => $model,
            'form'  => $form->createView()
        ]);
    }

    public function restore(Request $request)
    {
        $id = $request->request->get('id');
        $model = Group::onlyTrashed()->find($id);

        if (!$model) {
            return redirect()->route('dashboard.groups.index')
                ->with('message', ['warning', __('group.not_found')]);
        }

        $model->restore();

        return redirect()->route('dashboard.groups.index')
            ->with('message', ['success', __('group.restored', ['name' => $model->name])]);
    }

    public function purge(Request $request)
    {
        $id = $request->request->get('id');
        $model = Group::onlyTrashed()->find($id);

        if (!$model) {
            return redirect()->route('dashboard.groups.index')
                ->with('message', ['warning', __('group.not_found')]);
        }

        $model->forceDelete();

        return redirect()->route('dashboard.groups.index')
            ->with('message', ['success', __('group.purged', ['name' => $model->name])]);
    }
}
