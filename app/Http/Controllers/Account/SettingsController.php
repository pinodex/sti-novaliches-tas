<?php

/*
 * This file is part of the TAS System for STI College Novaliches
 *
 * (c) Raphael Marco <raphaelmarco@outlook.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Http\Controllers\Account;

use Auth;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Forms\SettingsForm;

class SettingsController extends Controller
{
    /**
     * User account settings page
     * 
     * @param \Illuminate\Http\Request $request Request object
     * 
     * @return mixed
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        if ($user->require_password_change) {
            session()->flash('message', ['warning', __('settings.password_change_required')]);
        }

        $form = with(new SettingsForm($user))
            ->getForm()
            ->handleRequest($request);

        if ($form->isValid()) {
            $user->fill($form->getData());
            $user->require_password_change = false;
            $user->save();

            return redirect()->route('dashboard.index')
                ->with('message', ['success', __('settings.password_updated')]);
        }

        return view('account.settings.index', [
            'form' => $form->createView()
        ]);
    }
}
