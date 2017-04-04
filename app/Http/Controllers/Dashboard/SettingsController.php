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
use App\Http\Forms\SettingsForm;

class SettingsController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        $form = with(new SettingsForm($user))
            ->getForm()
            ->handleRequest($request);

        if ($form->isValid()) {
            $user->fill($form->getData());
            $user->save();

            return redirect()->route('dashboard.index')
                ->with('message', ['success', __('settings.password_updated')]);
        }

        return view('dashboard.settings.index', [
            'form' => $form->createView()
        ]);
    }
}
