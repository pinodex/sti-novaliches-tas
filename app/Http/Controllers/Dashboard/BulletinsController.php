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

use Auth;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Forms\EditBulletinForm;
use App\Models\Bulletin;

class BulletinsController extends Controller
{
    public function index()
    {
        $bulletins = Bulletin::with('author', 'lastAuthor')->get();

        return view('dashboard.bulletins.index', [
            'bulletins' => $bulletins
        ]);
    }

    public function edit(Request $request, Bulletin $model)
    {
        $form = with(new EditBulletinForm($model))
            ->getForm()
            ->handleRequest($request);

        if ($form->isValid()) {
            $data = $form->getData();

            if (!$model->id) {
                $data['author_id'] = Auth::id();
            }

            if ($model->id) {
                $data['last_author_id'] = Auth::id();
            }

            $model->fill($data);
            $model->save();

            return redirect()->route('dashboard.bulletins.index')
                ->with('message', ['success', __('bulletin.created')]);
        }

        return view('dashboard.bulletins.edit', [
            'form'  => $form->createView(),
            'model' => $model
        ]);
    }

    public function delete(Request $request, Bulletin $model)
    {
        $model->delete();

        return redirect()->route('dashboard.bulletins.index')
            ->with('message', ['success', __('bulletin.deleted')]);
    }
}
