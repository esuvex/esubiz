<?php

namespace App\Services\Core\Notifications;

use App\Models\Notification;
use RuntimeException;

class WhatsappNotificationDriver implements NotificationChannelDriver
{
    public function send(Notification $notification): void
    {
        throw new RuntimeException(
            'WhatsApp notification provider is not configured.'
        );
    }
}
