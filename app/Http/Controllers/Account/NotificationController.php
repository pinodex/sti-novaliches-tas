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
                $entry = [
                    'id'            => $notification->id,
                    'data'          => $notification->data,
                    'read_at'       => $notification->read_at ? $notification->read_at->toDateTimeString() : null,
                    'created_at'    => $notification->created_at ? $notification->created_at->toDateTimeString() : null
                ];

                $entry['data']['link'] = route('notifications.view', [
                    'notification' => $notification
                ], false);

                $notifications['entries'][] = $entry;
            });

        return $notifications;
    }

    public function read(Request $request)
    {
        $ids = $request->input('ids');

        Auth::user()->notifications()
            ->whereIn('id', $ids)
            ->update([
                'read_at' => Carbon::now()
            ]);
    }
}
