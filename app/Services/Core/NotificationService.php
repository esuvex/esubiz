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
        ?int $websiteId = null,
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


        /*
         * ========================================================
         * CHECKPOINT 8 — CENTRAL SERVICE CREDIT BILLING
         * ========================================================
         *
         * Only website-bound metered services are billed here.
         *
         * Existing Central/internal notifications with no website_id
         * remain unchanged.
         */
        $billableService =
            in_array(
                $channel->type,
                [
                    'email',
                    'sms',
                    'whatsapp',
                ],
                true
            )
            && $websiteId !== null
            && $websiteId > 0;


        $creditService = null;
        $creditAmount = 0.0;


        if ($billableService) {

            $creditService =
                app(
                    \App\Services\CentralApi\CentralServiceCreditConsumptionService::class
                );


            /*
             * Initial charging model:
             *
             * 1 successful notification delivery = 1 service credit.
             *
             * Provider-specific pricing can later replace this with
             * exact segment/message/API-cost metering without changing
             * this authorization/ledger architecture.
             */
            $creditAmount =
                1.0;


            if (
                !$creditService->has(
                    $websiteId,
                    $channel->type,
                    $creditAmount
                )
            ) {
                throw new RuntimeException(
                    'Insufficient Esubiz '
                    . strtoupper(
                        $channel->type
                    )
                    . ' credits.'
                );
            }
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


            /*
             * Provider has succeeded.
             *
             * Only now may Central consume service credits.
             */
            if (
                $billableService
                && $creditService
            ) {

                $creditService->consume(
                    $websiteId,
                    $channel->type,
                    $creditAmount,
                    'notification:'
                        . $notification->uuid,
                    [
                        'user_id' =>
                            $userId,

                        'source_type' =>
                            'notification',

                        'source_id' =>
                            (string) $notification->uuid,

                        'metadata' => [
                            'notification_id' =>
                                $notification->id,

                            'notification_uuid' =>
                                $notification->uuid,

                            'channel' =>
                                $channel->type,

                            'template' =>
                                $template,

                            'recipient' =>
                                $recipient,

                            'reference' =>
                                $notification->reference,
                        ],
                    ]
                );
            }


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
