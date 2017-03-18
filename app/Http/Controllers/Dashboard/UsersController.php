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
            'result' => $users->paginate()
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

    public function validate(Request $request, User $model = null)
    {
        $errors = $this->form->validate($request->request->all());

        return response()->json($errors);
    }
}
