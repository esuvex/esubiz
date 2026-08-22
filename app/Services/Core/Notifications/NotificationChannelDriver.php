<?php

namespace App\Services\Core\Notifications;

use App\Models\Notification;

interface NotificationChannelDriver
{
    public function send(Notification $notification): void;
}
