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

use Image;
use Storage;
use Ramsey\Uuid\Uuid;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use App\Http\Controllers\Controller;
use App\Http\Forms\EditUserPictureForm;
use App\Http\Forms\EditUserForm;
use App\Models\User;
use App\Models\Group;
use App\Models\LeaveType;
use App\Models\UserPicture;
use App\Extensions\Acl;

class UsersController extends Controller
{
    public function __construct()
    {
        $this->can(Acl::MANAGE_USERS);
    }

    /**
     * Users index page
     * 
     * @param \Illuminate\Http\Request $request Request object
     * 
     * @return mixed
     */
    public function index(Request $request)
    {
        $users = User::with('group');
        $groups = Group::all();

        $showTrashed = $request->query->get('show') == 'deleted';
        $isAll = true;

        if ($showTrashed) {
            $users->onlyTrashed();
            $isAll = false;
        }

        if ($searchName = $request->query->get('name')) {
            $users->where('name', 'LIKE', '%' . $searchName . '%');
            $isAll = false;
        }

        if ($searchGroup = $request->query->get('group')) {
            $users->where(function (Builder $query) use ($searchGroup, &$isAll) {
                if ($searchGroup == 'unassigned') {
                    $isAll = false;
                    return $query->whereIn('group_id', [null, 0]);
                }

                if ($searchGroup != 'all') {
                    $isAll = false;
                    return $query->where('group_id', $searchGroup);
                }
            });
        }

        return view('dashboard.users.index', [
            'result' => $users->paginate(50),
            'groups' => $groups,
            'trash'  => $showTrashed,
            'is_all' => $isAll
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
        return view('dashboard.users.view', [
            'user'  => $model
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

            $model->fill($data);
            $model->save();
            
            $model->departments()->sync($data['departments']);

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
     * User picture edit page
     * 
     * @param \Illuminate\Http\Request $request Request object
     * @param \App\Models\User $model User model object
     * 
     * @return mixed
     */
    public function pictureEdit(Request $request, User $model)
    {
        $form = with(new EditUserPictureForm)
            ->getForm()
            ->handleRequest($request);

        if ($form->isValid()) {
            $uploadedImage = $form['picture']->getData();

            try {
                $picture = Image::make($uploadedImage);
            } catch (\Exception $e) {
                return redirect()->route('dashboard.users.picture.edit', [
                    'model' => $model
                ])->with('message', ['danger', __('user.picture_error')]);
            }

            $uuid = Uuid::uuid4()->toString();
            $storage = Storage::disk('public');

            $pictureTarget = sprintf('avatars/%s.picture.jpg', $uuid);
            $thumbTarget = sprintf('avatars/%s.thumb.jpg', $uuid);

            $storage->put($pictureTarget, $picture->fit(512)->stream('jpg', 75));
            $storage->put($thumbTarget, $picture->fit(64)->stream('jpg', 50));

            $storage->delete([
                $model->picture_path,
                $model->thumbnail_path
            ]);

            $model->picture_path = $pictureTarget;
            $model->thumbnail_path = $thumbTarget;
            
            $model->save();

            return redirect()->route('dashboard.users.view', [
                'model' => $model
            ])->with('message', ['success', __('user.picture_updated')]);
        }

        return view('dashboard.users.picture_edit', [
            'model' => $model,
            'form'  => $form->createView()
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

        $model->forceDelete();

        return redirect()->route('dashboard.users.deleted')
            ->with('message', ['success', __('user.purged', ['name' => $model->name])]);
    }
}
