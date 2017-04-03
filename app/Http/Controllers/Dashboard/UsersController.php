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
use App\Http\Forms\EditLeaveBalanceForm;
use App\Http\Forms\EditUserPictureForm;
use App\Http\Forms\EditUserForm;
use App\Models\User;
use App\Models\Group;
use App\Models\LeaveType;
use App\Models\LeaveBalance;
use App\Models\UserPicture;

class UsersController extends Controller
{
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

    public function deleted(Request $request)
    {
        $request->query->set('show', 'deleted');

        return $this->index($request);
    }

    public function view(Request $request, User $model)
    {
        $model->load([
            'group',
            'department',
            'departments',
            'leaveBalances',
            'leaveBalances.leaveType',
            'requests',
            'requests.approver',
            'requests.type'
        ]);

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

    public function pictureEdit(Request $request, User $model)
    {
        $model->load('picture');

        $form = with(new EditUserPictureForm)
            ->getForm()
            ->handleRequest($request);

        if ($form->isValid()) {
            $uploadedImage = $form['image']->getData();

            try {
                $image = Image::make($uploadedImage);
            } catch (\Exception $e) {
                return redirect()->route('dashboard.users.picture.edit', [
                    'model' => $model
                ])->with('message', ['danger', __('user.picture_error')]);
            }

            $uuid = Uuid::uuid4()->toString();
            $storage = Storage::disk('public');

            $imageTarget = sprintf('avatars/%s.image.jpg', $uuid);
            $thumbTarget = sprintf('avatars/%s.thumb.jpg', $uuid);

            $storage->put($imageTarget, $image->fit(512)->stream('jpg', 75));
            $storage->put($thumbTarget, $image->fit(64)->stream('jpg', 50));

            $userPicture = new UserPicture();
            $previousImage = null;
            $previousThumb = null;

            if ($model->picture) {
                $userPicture = $model->picture;
                
                $previousImage = $model->picture->image_path;
                $previousThumb = $model->picture->thumbnail_path;
            }

            $userPicture->fill([
                'user_id'           => $model->id,
                'image_path'        => $imageTarget,
                'thumbnail_path'    => $thumbTarget
            ]);

            $userPicture->save();

            $storage->delete([$previousImage, $previousThumb]);

            return redirect()->route('dashboard.users.view', [
                'model' => $model
            ])->with('message', ['success', __('user.picture_updated')]);
        }

        return view('dashboard.users.picture_edit', [
            'model' => $model,
            'form'  => $form->createView()
        ]);
    }

    public function balanceEdit(Request $request, User $model)
    {
        $model->load('leaveBalances');
        $leaveTypes = LeaveType::all();

        $form = with(new EditLeaveBalanceForm($model, $leaveTypes))
            ->getForm()
            ->handleRequest($request);

        if ($form->isValid()) {
            $data = $form->getData();

            foreach ($data['leave'] as $key => $value) {
                $query = LeaveBalance::where([
                    'user_id'       => $model->id,
                    'leave_type_id' => $key
                ]);

                if ($value == 0) {
                    $query->delete();

                    continue;
                }

                if ($query->count() > 0) {
                    $query->update([
                        'entitlement'   => $value
                    ]);

                    continue;
                }
                
                LeaveBalance::insert([
                    'user_id'       => $model->id,
                    'leave_type_id' => $key,
                    'entitlement'   => $value
                ]);
            }

            return redirect()->route('dashboard.users.view', [
                'model' => $model
            ])->with('message', ['success', __('user.edited', ['name' => $model->name])]);
        }

        return view('dashboard.users.balance_edit', [
            'user'  => $model,
            'types' => $leaveTypes,
            'form'  => $form->createView()
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
