<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class UserImportCompleted extends Notification
{
    use Queueable;

    public function via($notifiable)
    {
        return ['database'];
    }

    protected function getLink($notifiable)
    {
        return route('admin.users.index');
    }

    protected function getIcon($notifiable)
    {
        return 'upload';
    }

    protected function getContent($notifiable)
    {
        return 'User import completed';
    }
}
