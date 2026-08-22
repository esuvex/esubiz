<?php

namespace App\Services\Core\Notifications;

use App\Models\Notification;
use Illuminate\Support\Facades\Mail;

class EmailNotificationDriver implements NotificationChannelDriver
{
    public function send(Notification $notification): void
    {
        Mail::html(
            $notification->body,
            function ($message) use ($notification) {
                $message
                    ->to($notification->recipient)
                    ->subject($notification->subject ?? 'Esubiz Notification');
            }
        );
    }
}
