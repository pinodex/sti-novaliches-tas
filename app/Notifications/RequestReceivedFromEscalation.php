<?php

/*
 * This file is part of the TAS System for STI College Novaliches
 *
 * (c) Raphael Marco <raphaelmarco@outlook.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class RequestReceivedFromEscalation extends RequestResponded
{
    protected function getLink($notifiable)
    {
        return route('employee.requests.inbox.view', [
            'request' => $this->request
        ]);
    }
    
    protected function getImage($notifiable)
    {
        if (!$this->request->requestor) {
            return default_avatar_thumb();
        }

        return $this->request->requestor->picture['thumb'];
    }

    protected function getContent($notifiable)
    {
        if (!$this->request->requestor) {
            return 'A request has been escalated to you';
        }

        return 'A request by ' . $this->request->requestor->name . ' has been escalated to you';
    }
}
