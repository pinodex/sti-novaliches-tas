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
use App\Http\Forms\EditProfileForm;
use App\Models\Profile;
use App\Components\Acl;

class ProfileController extends Controller
{
    public function __construct()
    {
        $this->can(Acl::ADMIN_PROFILES);
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
        $result = Profile::all();

        return view('admin.profiles.index', [
            'result'    => $result
        ]);
    }

    /**
     * Department edit page
     * 
     * @param \Illuminate\Http\Request $request Request object
     * @param \App\Models\Department $model Department model object
     * 
     * @return mixed
     */
    public function edit(Request $request, Profile $model)
    {
        $editMode = $model->id !== null;

        $form = with(new EditProfileForm($model))
            ->getForm()
            ->handleRequest($request);

        if ($form->isValid()) {
            $data = $form->getData();

            $model->fill($data);
            $model->save();

            return redirect()->route('admin.profiles.index')
                ->with('message', ['success',
                    $editMode ? __('profile.edited', ['name' => $model->name]) :
                        __('profile.added', ['name' => $model->name])
                ]);
        }

        return view('admin.profiles.edit', [
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
    public function delete(Request $request, Profile $model)
    {
        $model->delete();

        return redirect()->route('admin.profiles.index')
            ->with('message', ['success', __('profile.deleted', ['name' => $model->name])]);
    }
}
