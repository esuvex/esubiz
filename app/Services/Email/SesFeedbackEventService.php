<?php

namespace App\Services\Email;

use App\Models\Email\EmailUsageLog;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

/**
 * ESUBIZ_SES_FEEDBACK_EVENT_SERVICE_V3_USAGE_LOG_ID
 *
 * Applies authenticated Amazon SES feedback events to the canonical
 * EmailUsageLog lifecycle.
 *
 * Important:
 * - SNS authentication/signature validation belongs to the HTTP receiver.
 * - This service accepts only the inner SES notification payload.
 * - Correlation is by immutable SES provider message ID.
 * - Event processing is idempotent.
 * - Delivery/bounce/complaint/reject events never alter credit billing.
 * - Later adverse events may supersede an earlier delivery state.
 */
class SesFeedbackEventService
{
    /**
     * Apply one SES notification payload.
     *
     * @return array<string, mixed>
     */
    public function handle(array $payload): array
    {
        $notificationType = $this->notificationType($payload);
        $messageId = trim((string) data_get($payload, 'mail.messageId', ''));

        if ($messageId === '') {
            throw new RuntimeException(
                'SES feedback payload does not contain mail.messageId.'
            );
        }

        $event = $this->normaliseEvent($notificationType, $payload);

        return DB::transaction(function () use (
            $payload,
            $notificationType,
            $messageId,
            $event
        ): array {
            /** @var EmailUsageLog|null $usage */
            $usage = EmailUsageLog::query()
                ->where('provider', 'aws')
                ->where('provider_service', 'ses')
                ->where('provider_message_id', $messageId)
                ->lockForUpdate()
                ->first();

            /*
             * SNS can legitimately deliver feedback before provider acceptance
             * has finished persisting provider_message_id.
             *
             * Never fabricate an EmailUsageLog row here: tenant ownership,
             * pricing and billing context belong exclusively to the outbound
             * send lifecycle.
             *
             * Also do not acknowledge an unmatched event as successfully
             * processed. The HTTP receiver will translate this exception into
             * a retryable non-2xx response so SNS can redeliver it after the
             * provider-acceptance transaction has completed.
             */
            if (!$usage) {
                Log::warning('SES feedback event has no matching usage log yet.', [
                    'provider_message_id' => $messageId,
                    'notification_type' => $notificationType,
                ]);

                throw new RuntimeException(
                    "SES feedback event cannot yet be correlated to provider message [{$messageId}]."
                );
            }

            $metadata = is_array($usage->metadata)
                ? $usage->metadata
                : [];

            $history = data_get($metadata, 'ses_feedback.events', []);

            if (!is_array($history)) {
                $history = [];
            }

            $eventKey = $this->eventKey(
                $notificationType,
                $messageId,
                $event
            );

            foreach ($history as $historicalEvent) {
                if (
                    is_array($historicalEvent)
                    && hash_equals(
                        (string) ($historicalEvent['event_key'] ?? ''),
                        $eventKey
                    )
                ) {
                    return [
                        'ok' => true,
                        'matched' => true,
                        'provider_message_id' => $messageId,
                        'email_usage_log_id' => (int) $usage->getKey(),
                        'notification_type' => $notificationType,
                        'status' => $usage->status,
                        'already_processed' => true,
                    ];
                }
            }

            $currentStatus = strtolower(trim((string) $usage->status));
            $incomingStatus = $event['status'];

            if ($this->shouldApplyStatus($currentStatus, $incomingStatus)) {
                $usage->status = $incomingStatus;
            }

            /*
             * completed_at represents a terminal provider feedback state.
             * Delivery, bounce, complaint and rejection are all terminal
             * observations for this outbound attempt.
             */
            if (!$usage->completed_at) {
                $usage->completed_at = $event['occurred_at'];
            } elseif (
                in_array($incomingStatus, [
                    'bounced',
                    'complained',
                    'rejected',
                ], true)
            ) {
                /*
                 * If an adverse event supersedes delivery, retain the most
                 * recent adverse event timestamp as the completion marker.
                 */
                $usage->completed_at = $event['occurred_at'];
            }

            $history[] = [
                'event_key' => $eventKey,
                'notification_type' => $notificationType,
                'status' => $incomingStatus,
                'occurred_at' => $event['occurred_at']->toIso8601String(),
                'received_at' => now()->toIso8601String(),
                'details' => $event['details'],
            ];

            /*
             * Bound metadata growth. SES/SNS retries are deduplicated above,
             * but this also protects the operational row from unbounded event
             * history if AWS produces many distinct events.
             */
            if (count($history) > 50) {
                $history = array_slice($history, -50);
            }

            data_set($metadata, 'ses_feedback.last_notification_type', $notificationType);
            data_set($metadata, 'ses_feedback.last_status', $incomingStatus);
            data_set($metadata, 'ses_feedback.last_event_at', $event['occurred_at']->toIso8601String());
            data_set($metadata, 'ses_feedback.events', $history);

            $usage->metadata = $metadata;
            $usage->save();

            return [
                'ok' => true,
                'matched' => true,
                'provider_message_id' => $messageId,
                'email_usage_log_id' => (int) $usage->getKey(),
                'notification_type' => $notificationType,
                'status' => $usage->status,
                'already_processed' => false,
            ];
        });
    }

    private function notificationType(array $payload): string
    {
        $type = trim((string) (
            $payload['notificationType']
            ?? $payload['eventType']
            ?? ''
        ));

        if ($type === '') {
            throw new RuntimeException(
                'SES feedback payload does not contain notificationType.'
            );
        }

        return strtolower($type);
    }

    /**
     * @return array{
     *     status:string,
     *     occurred_at:CarbonImmutable,
     *     details:array<string,mixed>
     * }
     */
    private function normaliseEvent(
        string $notificationType,
        array $payload
    ): array {
        return match ($notificationType) {
            'delivery' => [
                'status' => 'delivered',
                'occurred_at' => $this->timestamp(
                    data_get($payload, 'delivery.timestamp')
                    ?? data_get($payload, 'mail.timestamp')
                ),
                'details' => [
                    'processing_time_millis' =>
                        data_get($payload, 'delivery.processingTimeMillis'),
                    'smtp_response' =>
                        data_get($payload, 'delivery.smtpResponse'),
                    'reporting_mta' =>
                        data_get($payload, 'delivery.reportingMTA'),
                    'recipients' =>
                        data_get($payload, 'delivery.recipients', []),
                ],
            ],

            'bounce' => [
                'status' => 'bounced',
                'occurred_at' => $this->timestamp(
                    data_get($payload, 'bounce.timestamp')
                    ?? data_get($payload, 'mail.timestamp')
                ),
                'details' => [
                    'bounce_type' =>
                        data_get($payload, 'bounce.bounceType'),
                    'bounce_sub_type' =>
                        data_get($payload, 'bounce.bounceSubType'),
                    'reporting_mta' =>
                        data_get($payload, 'bounce.reportingMTA'),
                    'recipients' =>
                        data_get($payload, 'bounce.bouncedRecipients', []),
                ],
            ],

            'complaint' => [
                'status' => 'complained',
                'occurred_at' => $this->timestamp(
                    data_get($payload, 'complaint.timestamp')
                    ?? data_get($payload, 'mail.timestamp')
                ),
                'details' => [
                    'feedback_type' =>
                        data_get($payload, 'complaint.complaintFeedbackType'),
                    'user_agent' =>
                        data_get($payload, 'complaint.userAgent'),
                    'recipients' =>
                        data_get($payload, 'complaint.complainedRecipients', []),
                ],
            ],

            'reject' => [
                'status' => 'rejected',
                'occurred_at' => $this->timestamp(
                    data_get($payload, 'mail.timestamp')
                ),
                'details' => [
                    'reason' => data_get($payload, 'reject.reason'),
                ],
            ],

            default => throw new RuntimeException(
                "Unsupported SES notification type [{$notificationType}]."
            ),
        };
    }

    private function timestamp(mixed $value): CarbonImmutable
    {
        try {
            if (is_string($value) && trim($value) !== '') {
                return CarbonImmutable::parse($value);
            }
        } catch (Throwable) {
            // Fall through to receipt time.
        }

        return CarbonImmutable::now();
    }

    /**
     * Generate a stable event key without storing the complete raw payload.
     *
     * @param array{
     *     status:string,
     *     occurred_at:CarbonImmutable,
     *     details:array<string,mixed>
     * } $event
     */
    private function eventKey(
        string $notificationType,
        string $messageId,
        array $event
    ): string {
        return hash('sha256', json_encode([
            'notification_type' => $notificationType,
            'provider_message_id' => $messageId,
            'status' => $event['status'],
            'occurred_at' => $event['occurred_at']->toIso8601String(),
            'details' => $event['details'],
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }

    private function shouldApplyStatus(
        string $currentStatus,
        string $incomingStatus
    ): bool {
        /*
         * Provider acceptance/billing lifecycle may advance to provider_accepted
         * before SES feedback arrives. Feedback states supersede those states.
         *
         * Adverse feedback must also be able to supersede "delivered" because
         * complaints and some bounce reports can arrive later.
         */
        $priority = [
            'pending' => 0,
            'provider_accepted_billing_pending' => 10,
            'provider_accepted' => 20,
            'delivered' => 30,
            'rejected' => 40,
            'bounced' => 50,
            'complained' => 60,
        ];

        $currentPriority = $priority[$currentStatus] ?? 0;
        $incomingPriority = $priority[$incomingStatus] ?? 0;

        return $incomingPriority >= $currentPriority;
    }
}
