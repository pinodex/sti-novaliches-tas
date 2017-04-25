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
use App\Notifications\NotificationReader;
use Illuminate\Notifications\DatabaseNotification;

class NotificationController extends Controller
{
    public function __construct()
    {
        $this->middleware('provider:employee');
    }

    public function index()
    {
        $notifications = [];

        Auth::user()
            ->unreadNotifications()
            ->orderBy('created_at', 'DESC')
            ->each(function (DatabaseNotification $notification) use (&$notifications) {
                $notifications[] = $this->show($notification);
            });

        return $notifications;
    }

    public function show(DatabaseNotification $notification)
    {
        $nr = new NotificationReader($notification);

        return [
            'id' => $notification->id,
            'title' => $nr->getTitle(),
            'content' => $nr->getContent(),
            'read_at' => $notification->read_at ? $notification->read_at->toDateTimeString() : null,
            'created_at' => $notification->created_at ? $notification->created_at->toDateTimeString() : null
        ];
    }

    public function update(DatabaseNotification $notification)
    {
        $notification->markAsRead();
    }
}
