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
use App\Models\Request;

class RequestResponded extends Notification implements ShouldQueue
{
    use Queueable;
    
    /**
     * @var \App\Models\Request Request object instance
     */
    protected $request;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['database', 'mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $mapping = config('notification.mapping');
        $types = config('request.types');
        
        $notifClass = get_class($this);

        $entry = $mapping[$notifClass];
        $url = url(route('employee.requests.view', [
            'model' => $this->request 
        ]));

        if ($notifClass == RequestReceived::class || $notifClass == RequestReceivedFromEscalation::class) {
            $url = url(route('employee.requests.inbox.view', [
                'model' => $this->request 
            ]));
        }

        $message = (new MailMessage)
            ->subject(sprintf('%s: %s', $entry['title'], $this->request->type_name))
            ->markdown('emails.request_responded', [
                'entry'     => $entry,
                'recipient' => $notifiable,
                'request'   => $this->request,
                'url'       => $url
            ]);

        return $message;
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        $types = config('request.types');
        $approverName = null;

        if ($this->request->approver) {
            $approverName = $this->request->approver->name;
        }

        return [
            'request_id' => $this->request->id,
            'type_name' => $this->request->type_name,
            'approver_name' => $approverName,
            'time' => $this->request->responded_at
        ];
    }
}
