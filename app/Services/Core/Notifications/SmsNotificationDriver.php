<?php

namespace App\Services\Core\Notifications;

use App\Models\Notification;
use RuntimeException;

class SmsNotificationDriver implements NotificationChannelDriver
{
    public function send(Notification $notification): void
    {
        throw new RuntimeException(
            'SMS notification provider is not configured.'
        );
    }
}
