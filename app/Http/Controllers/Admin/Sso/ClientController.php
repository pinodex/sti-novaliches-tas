<?php

/*
 * This file is part of the TAS System for STI College Novaliches
 *
 * (c) Raphael Marco <raphaelmarco@outlook.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Http\Controllers\Admin\Sso;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Forms\EditSsoClientForm;
use App\Models\Sso\Client as SsoClient;

class ClientController extends Controller
{
    /**
     * Sso index page
     * 
     * @param \Illuminate\Http\Request $request Request object
     * 
     * @return mixed
     */
    public function index()
    {
        $clients = SsoClient::paginate(50);

        return view('admin.sso.index', [
            'result' => $clients
        ]);
    }

    /**
     * Sso Clients edit page
     * 
     * @param \Illuminate\Http\Request $request Request object
     * @param \App\Models\Sso\Client $model Sso Client model
     * 
     * @return mixed
     */
    public function edit(Request $request, SsoClient $model)
    {
        $editMode = $model->id !== null;

        $form = with(new EditSsoClientForm($model))
            ->getForm()
            ->handleRequest($request);

        if ($form->isValid()) {
            $data = $form->getData();

            $model->fill($data);

            if (!$editMode) {
                $model->generateSecret();
            }

            $model->save();

            $this->logAction('sso_client_saved', [
                'name'  => $model->name
            ]);

            return redirect()->route('admin.sso.clients.view', [
                'model' => $model
            ])->with('message', ['success',
                $editMode ? __('sso.edited', ['name' => $model->name]) :
                    __('sso.added', ['name' => $model->name])
            ]);
        }

        return view('admin.sso.clients.edit', [
            'form'  => $form->createView(),
            'model' => $model
        ]);
    }

    /**
     * Sso Clients view page
     * 
     * @param \Illuminate\Http\Request $request Request object
     * @param \App\Models\Sso\Client $model Sso Client model
     * 
     * @return mixed
     */
    public function view(Request $request, SsoClient $model)
    {
        return view('admin.sso.clients.view', [
            'model' => $model
        ]);
    }

    /**
     * Sso Clients reset page
     * 
     * @param \Illuminate\Http\Request $request Request object
     * @param \App\Models\Sso\Client $model Sso Client model
     * 
     * @return mixed
     */
    public function resetSecret(Request $request, SsoClient $model)
    {
        $model->generateSecret();
        $model->save();

        return redirect()->route('admin.sso.clients.view', [
            'model' => $model
        ])->with('message', ['success', __('sso.secret_generated')]);
    }

    /**
     * User delete action
     * 
     * @param \Illuminate\Http\Request $request Request object
     * @param \App\Models\Sso\Client $model Sso Client model
     * 
     * @return mixed
     */
    public function delete(Request $request, SsoClient $model)
    {
        $model->delete();

        $this->logAction('sso_deleted', [
            'name'  => $model->name
        ]);

        return redirect()->route('admin.sso.index')
            ->with('message', ['success', __('sso.deleted', ['name' => $model->name])]);
    }
}
