<?php

namespace App\Services\Core\Notifications;

use RuntimeException;

class NotificationDriverFactory
{
    public function make(string $type): NotificationChannelDriver
    {
        return match ($type) {
            'email' => app(EmailNotificationDriver::class),
            'sms' => app(SmsNotificationDriver::class),
            'whatsapp' => app(WhatsappNotificationDriver::class),
            'push' => app(PushNotificationDriver::class),
            default => throw new RuntimeException(
                "Unsupported notification channel [{$type}]."
            ),
        };
    }
}
