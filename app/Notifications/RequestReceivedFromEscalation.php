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
    public function toArray($notifiable)
    {
        $types = config('request.types');
        
        $typeName = $this->request->type;
        $requestorName = null;

        if (array_key_exists($this->request->type, $types)) {
            $typeName = $types[$this->request->type]::getName();
        }

        if ($this->request->requestor) {
            $requestorName = $this->request->requestor->name;
        }

        return [
            'request_id' => $this->request->id,
            'type_name' => $typeName,
            'requestor_name' => $requestorName,
            'time' => $this->request->responded_at
        ];
    }
}
