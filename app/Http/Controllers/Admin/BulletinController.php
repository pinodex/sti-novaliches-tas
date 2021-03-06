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

use Auth;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Forms\EditBulletinForm;
use App\Models\Bulletin;
use App\Components\Acl;

class BulletinController extends Controller
{
    public function __construct()
    {
        $this->can(Acl::ADMIN_BULLETIN);
    }

    /**
     * Bulletins index page
     * 
     * @return mixed
     */
    public function index()
    {
        $bulletins = Bulletin::orderBy('created_at', 'DESC')
            ->with('author', 'lastAuthor')
            ->get();

        return view('admin.bulletins.index', [
            'bulletins' => $bulletins
        ]);
    }

    /**
     * Bulletin edit page
     * 
     * @param \Illuminate\Http\Request $request Request object
     * @param \App\Models\Bulletin $model Bulletin model object
     * 
     * @return mixed
     */
    public function edit(Request $request, Bulletin $model)
    {
        $form = with(new EditBulletinForm($model))
            ->getForm()
            ->handleRequest($request);

        if ($form->isValid()) {
            $data = $form->getData();

            if (!$model->id) {
                // The model is fresh
                $data['author_id'] = Auth::user()->id;
            }

            if ($model->id) {
                // The model is edited
                $data['last_author_id'] = Auth::user()->id;
            }

            $model->fill($data);
            $model->save();

            $this->logAction('bulletin_saved', [
                'title' => $model->title
            ]);

            return redirect()->route('admin.bulletins.index')
                ->with('message', ['success', __('bulletin.created')]);
        }

        return view('admin.bulletins.edit', [
            'form'  => $form->createView(),
            'model' => $model
        ]);
    }

    /**
     * Bulletin delete page
     * 
     * @param \Illuminate\Http\Request $request Request object
     * @param \App\Models\Bulletin $model Bulletin model object
     * 
     * @return mixed
     */
    public function delete(Request $request, Bulletin $model)
    {
        $model->delete();

        $this->logAction('bulletin_deleted', [
            'title' => $model->title
        ]);

        return redirect()->route('admin.bulletins.index')
            ->with('message', ['success', __('bulletin.deleted')]);
    }
}
