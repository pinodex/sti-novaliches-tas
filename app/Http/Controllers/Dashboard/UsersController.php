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
    public function index(Request $request)
    {
        $users = User::with('group')->paginate();

        return view('dashboard.users.index', [
            'result' => $users
        ]);
    }

    public function edit(Request $request, User $model = null)
    {
        $form = (new EditUserForm())->setModel($model);

        $form = $form->getForm();
        $form->handleRequest($request);

        if ($form->isValid()) {
            
        }

        return view('dashboard.users.edit', [
            'form'  => $form->createView()
        ]);
    }
}
