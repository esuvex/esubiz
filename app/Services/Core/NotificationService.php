<?php

namespace App\Services\Core;

use App\Models\Notification;
use App\Models\NotificationChannel;
use App\Models\NotificationTemplate;
use App\Services\Core\Notifications\NotificationDriverFactory;
use Illuminate\Support\Str;
use RuntimeException;

class NotificationService
{
    public function __construct(
        protected NotificationDriverFactory $driverFactory
    ) {
    }

    public function send(
        string $type,
        string $template,
        string $recipient,
        array $variables = [],
        ?int $userId = null,
        ?int $workspaceId = null,
        ?string $reference = null,
        $notifiable = null,
    ): Notification {
        $channel = NotificationChannel::query()
            ->where('type', $type)
            ->where('is_enabled', true)
            ->where(function ($query) use ($workspaceId) {
                $query->where('workspace_id', $workspaceId)
                    ->orWhereNull('workspace_id');
            })
            ->orderByDesc('is_default')
            ->first();

        if (!$channel) {
            throw new RuntimeException(
                "No enabled {$type} notification channel is configured."
            );
        }

        $notificationTemplate = NotificationTemplate::query()
            ->where('notification_channel_id', $channel->id)
            ->where('slug', $template)
            ->where('is_enabled', true)
            ->where(function ($query) use ($workspaceId) {
                $query->where('workspace_id', $workspaceId)
                    ->orWhereNull('workspace_id');
            })
            ->orderByDesc('is_default')
            ->first();

        if (!$notificationTemplate) {
            throw new RuntimeException(
                "Notification template [{$template}] was not found."
            );
        }

        $subject = $this->render(
            $notificationTemplate->subject ?? '',
            $variables
        );

        $body = $this->render(
            $notificationTemplate->body,
            $variables
        );

        $notification = Notification::create([
            'workspace_id' => $workspaceId,
            'notification_channel_id' => $channel->id,
            'notification_template_id' => $notificationTemplate->id,
            'user_id' => $userId,
            'uuid' => (string) Str::uuid(),
            'reference' => $reference ?: 'NTF-' . strtoupper(Str::random(16)),
            'recipient' => $recipient,
            'subject' => $subject,
            'body' => $body,
            'notifiable_type' => $notifiable ? get_class($notifiable) : null,
            'notifiable_id' => $notifiable?->getKey(),
            'status' => 'sending',
        ]);

        try {
            $driver = $this->driverFactory->make($channel->type);

            $driver->send($notification);

            $notification->update([
                'status' => 'sent',
                'sent_at' => now(),
            ]);
        } catch (\Throwable $e) {
            $notification->update([
                'status' => 'failed',
                'provider_response' => [
                    'message' => $e->getMessage(),
                ],
            ]);

            throw $e;
        }

        return $notification;
    }

    protected function render(string $content, array $variables): string
    {
        foreach ($variables as $key => $value) {
            if (is_scalar($value) || $value === null) {
                $replacement = (string) ($value ?? '');
            } else {
                $replacement = json_encode(
                    $value,
                    JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
                );
            }

            $content = str_replace(
                [
                    '{{' . $key . '}}',
                    '{{ ' . $key . ' }}',
                ],
                $replacement,
                $content
            );
        }

        return $content;
    }
}
