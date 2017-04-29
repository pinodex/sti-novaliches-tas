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
use App\Http\Forms\EditGroupForm;
use App\Http\Forms\DeleteGroupConfirmForm;
use App\Components\Acl;
use App\Models\Group;

class GroupController extends Controller
{
    public function __construct()
    {
        $this->can(Acl::ADMIN_GROUPS);
    }

    /**
     * Groups index page
     * 
     * @param \Illuminate\Http\Request $request Request object
     * 
     * @return mixed
     */
    public function index(Request $request)
    {
        $groups = Group::withCount('users');

        $showTrashed = $request->query->get('show') == 'deleted';
        $isAll = true;

        if ($showTrashed) {
            $groups->onlyTrashed();
            $isAll = false;
        }

        if ($searchName = $request->query->get('name')) {
            $groups->where('name', 'LIKE', '%' . $searchName . '%');
            $isAll = false;
        }

        return view('admin.groups.index', [
            'result' => $groups->paginate(50),
            'groups' => $groups,
            'trash'  => $showTrashed,
            'is_all' => $isAll
        ]);
    }

    /**
     * Deleted groups page. Calls index route with show=delete query param
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
     * Group edit page
     * 
     * @param \Illuminate\Http\Request $request Request object
     * @param \App\Models\Group $model Group model object
     * 
     * @return mixed
     */
    public function edit(Request $request, Group $model)
    {
        $editMode = $model->id !== null;

        $form = with(new EditGroupForm($model))
            ->getForm()
            ->handleRequest($request);

        if ($form->isValid()) {
            $data = $form->getData();

            $model->fill($data);
            $model->save();

            $this->logAction('group_saved', [
                'name'  => $model->name
            ]);

            return redirect()->route('admin.groups.index')
                ->with('message', ['success',
                    $editMode ? __('group.edited', ['name' => $model->name]) :
                        __('group.added', ['name' => $model->name])
                ]);
        }

        return view('admin.groups.edit', [
            'form'  => $form->createView(),
            'model' => $model
        ]);
    }

    /**
     * Group delete action
     * 
     * @param \Illuminate\Http\Request $request Request object
     * @param \App\Models\Group $model Group model object
     * 
     * @return mixed
     */
    public function delete(Request $request, Group $model)
    {
        if ($model->users->count() > 0) {
            return redirect()->route('admin.groups.delete.confirm', ['model' => $model]);
        }

        $model->delete();

        $this->logAction('group_deleted', [
            'name'  => $model->name
        ]);

        return redirect()->route('admin.groups.index')
            ->with('message', ['success', __('group.deleted', ['name' => $model->name])]);
    }

    /**
     * Group delete confirmation page
     * 
     * @param \Illuminate\Http\Request $request Request object
     * @param \App\Models\Group $model Group model object
     * 
     * @return mixed
     */
    public function deleteConfirm(Request $request, Group $model)
    {
        $form = new DeleteGroupConfirmForm($model);

        $form = $form->getForm();
        $form->handleRequest($request);

        if ($form->isValid()) {
            $action = $form['action']->getData();
            $targetGroup = $form['group']->getData();

            if (!$targetGroup) {
                $targetGroup = null;
            }

            DB::transaction(function () use ($model, $action, $targetGroup) {
                switch ($action) {                    
                    case 'move':
                        $model->users()->update([
                            'group_id' => $targetGroup
                        ]);

                        $targetGroupModel = Group::find($targetGroup);

                        $this->logAction('group_users_moved', [
                            'from'  => $model->name,
                            'to'    => $targetGroupModel ? $targetGroupModel->name : 'Unassigned'
                        ]);

                        break;

                    case 'delete':
                        $model->users()->delete();

                        $this->logAction('group_users_deleted', [
                            'name'  => $model->name
                        ]);

                        break;
                }

                $model->delete();
            });

            return redirect()->route('admin.groups.index')
                ->with('message', ['success', __('group.deleted', ['name' => $model->name])]);
        }

        return view('admin.groups.confirm', [
            'model' => $model,
            'form'  => $form->createView()
        ]);
    }

    /**
     * Group restore action
     * 
     * @param \Illuminate\Http\Request $request Request object
     * 
     * @return mixed
     */
    public function restore(Request $request)
    {
        $id = $request->request->get('id');
        $model = Group::onlyTrashed()->find($id);

        if (!$model) {
            return redirect()->route('admin.groups.index')
                ->with('message', ['warning', __('group.not_found')]);
        }

        $model->restore();

        $this->logAction('group_restored', [
            'name'  => $model->name
        ]);

        return redirect()->route('admin.groups.index')
            ->with('message', ['success', __('group.restored', ['name' => $model->name])]);
    }

    /**
     * Group permanent delete action
     * 
     * @param \Illuminate\Http\Request $request Request object
     * 
     * @return mixed
     */
    public function purge(Request $request)
    {
        $id = $request->request->get('id');
        $model = Group::onlyTrashed()->find($id);

        if (!$model) {
            return redirect()->route('admin.groups.index')
                ->with('message', ['warning', __('group.not_found')]);
        }

        $model->forceDelete();

        $this->logAction('group_purged', [
            'name'  => $model->name
        ]);

        return redirect()->route('admin.groups.index')
            ->with('message', ['success', __('group.purged', ['name' => $model->name])]);
    }
}
