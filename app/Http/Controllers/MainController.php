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
        $notifications = Auth::user()->notifications()->paginate(50);

        return view('notifications', [
            'notifications' => $notifications
        ]);
    }

    /**
     * View notification action
     * 
     * @param \Illuminate\Http\Request $request Request object
     * 
     * @return mixed
     */
    public function viewNotification(Request $request, DatabaseNotification $notification)
    {
        $notification->markAsRead();

        if (array_key_exists('link', $notification->data) && $notification->data['link']) {
            return redirect($notification->data['link']);
        }

        return redirect('/');
    }
}
