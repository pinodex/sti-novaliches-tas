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
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Notifications\NotificationReader;
use Illuminate\Notifications\DatabaseNotification;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = [
            'unread_count'  => 0,
            'entries'       => []
        ];

        $notifications['unread_count'] = Auth::user()->unreadNotifications()->count();

        Auth::user()
            ->unreadNotifications()
            ->orderBy('created_at', 'DESC')
            ->each(function (DatabaseNotification $notification) use (&$notifications) {
                $notifications['entries'][] = $this->view($notification);
            });

        return $notifications;
    }

    public function view(DatabaseNotification $model)
    {
        $nr = new NotificationReader($model);

        return [
            'id' => $model->id,
            'title' => $nr->getTitle(),
            'content' => $nr->getContent(),
            'read_at' => $model->read_at ? $model->read_at->toDateTimeString() : null,
            'created_at' => $model->created_at ? $model->created_at->toDateTimeString() : null
        ];
    }

    public function read(Request $request)
    {
        return;
        $ids = $request->input('ids');

        Auth::user()->notifications()
            ->whereIn('id', $ids)
            ->update([
                'read_at' => Carbon::now()
            ]);
    }
}
