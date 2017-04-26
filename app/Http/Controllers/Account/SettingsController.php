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
    public function __invoke(Request $request)
    {
        $user = Auth::user();

        $form = with(new SettingsForm($user))
            ->getForm()
            ->handleRequest($request);

        if ($form->isValid()) {
            $data = $form->getData();

            $user->fill($data);
            
            $user->require_password_change = false;
            
            $user->save();

            $user->log('change_password');

            return $user->getRedirectAction()
                ->with('message', ['success', __('settings.password_updated')]);
        }

        return view('account.settings.index', [
            'form' => $form->createView(),
            'password_change_required' => $user->require_password_change
        ]);
    }
}
