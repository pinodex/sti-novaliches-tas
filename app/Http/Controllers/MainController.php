<?php

/*
 * This file is part of the TAS System for STI College Novaliches
 *
 * (c) Raphael Marco <raphaelmarco@outlook.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Http\Controllers;

use Auth;
use Illuminate\Http\Request;
use App\Notifications\NotificationReader;
use Illuminate\Notifications\DatabaseNotification;

class MainController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Index page
     * 
     * @return mixed
     */
    public function index()
    {
        return view('index');
    }

    /**
     * Notifications page
     * 
     * @return mixed
     */
    public function notifications()
    {
        $notifications = [];

        Auth::user()->notifications->each(function (DatabaseNotification $model) use (&$notifications) {
            $nr = new NotificationReader($model);

            $notifications[] = [
                'id' => $model->id,
                'title' => $nr->getTitle(),
                'content' => $nr->getContent(),
                'read_at' => $model->read_at ? $model->read_at->toDateTimeString() : null,
                'created_at' => $model->created_at ? $model->created_at->toDateTimeString() : null
            ];
        });

        return view('notifications', [
            'notifications' => $notifications
        ]);
    }
}
