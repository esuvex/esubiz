<?php

namespace App\Services\Core\Notifications;

use App\Models\Notification;
use RuntimeException;

class PushNotificationDriver implements NotificationChannelDriver
{
    public function send(Notification $notification): void
    {
        throw new RuntimeException(
            'Push notification provider is not configured.'
        );
    }
}
