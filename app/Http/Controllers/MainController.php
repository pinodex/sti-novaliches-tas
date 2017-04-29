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
use Illuminate\Pagination\LengthAwarePaginator;

class MainController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('require_password_change');
    }

    /**
     * Index page
     * 
     * @return mixed
     */
    public function index()
    {
        $model = Auth::user();
        
        $requests = $model->requests()
            ->orderBy('created_at', 'DESC')
            ->limit(5)
            ->get();

        $inbox = $model->inbox()
            ->orderBy('created_at', 'DESC')
            ->limit(5)
            ->get();

        return view('index', [
            'model'     => $model,
            'requests'  => $requests,
            'inbox'     => $inbox
        ]);
    }

    /**
     * Notifications page
     * 
     * @return mixed
     */
    public function notifications()
    {
        $pagination = Auth::user()->notifications()->paginate(50);
        $notifications = $pagination->map(function (DatabaseNotification $model) use (&$notifications) {
                $nr = new NotificationReader($model);

                return [
                    'id' => $model->id,
                    'title' => $nr->getTitle(),
                    'content' => $nr->getContent(),
                    'read_at' => $model->read_at ? $model->read_at->toDateTimeString() : null,
                    'created_at' => $model->created_at ? $model->created_at->toDateTimeString() : null
                ];
            });

        return view('notifications', [
            'notifications' => $notifications,
            'pagination'    => $pagination
        ]);
    }
}
