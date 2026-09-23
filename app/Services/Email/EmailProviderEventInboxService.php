<?php

namespace App\Services\Email;

use App\Models\Email\EmailProviderEvent;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;
use Throwable;

/**
 * ESUBIZ_EMAIL_PROVIDER_EVENT_INBOX_V1
 *
 * Durable/idempotent inbox for authenticated provider events.
 *
 * Authentication is intentionally NOT performed here. The SNS controller
 * must cryptographically validate the SNS message before calling this
 * service.
 */
class EmailProviderEventInboxService
{
    public function available(): bool
    {
        try {
            return Schema::hasTable('email_provider_events');
        } catch (Throwable) {
            return false;
        }
    }

    /**
     * Persist an already-authenticated AWS SNS Notification.
     *
     * Re-delivery of the same SNS MessageId returns the existing record
     * rather than creating a second provider event.
     */
    public function receive(
        array $snsEnvelope,
        array $providerPayload
    ): EmailProviderEvent {
        if (!$this->available()) {
            throw new RuntimeException(
                'Email provider event inbox is not available yet.'
            );
        }

        $providerEventId = trim(
            (string) ($snsEnvelope['MessageId'] ?? '')
        );

        if ($providerEventId === '') {
            throw new RuntimeException(
                'Authenticated SNS message has no MessageId.'
            );
        }

        $providerMessageId = trim(
            (string) (
                $providerPayload['mail']['messageId']
                ?? ''
            )
        );

        $eventType = trim(
            (string) (
                $providerPayload['notificationType']
                ?? $providerPayload['eventType']
                ?? ''
            )
        );

        return DB::transaction(
            function () use (
                $snsEnvelope,
                $providerPayload,
                $providerEventId,
                $providerMessageId,
                $eventType
            ): EmailProviderEvent {
                $existing = EmailProviderEvent::query()
                    ->where(
                        'provider_event_id',
                        $providerEventId
                    )
                    ->lockForUpdate()
                    ->first();

                if ($existing !== null) {
                    return $existing;
                }

                try {
                    return EmailProviderEvent::query()->create([
                        'provider' => 'aws',
                        'provider_service' => 'ses',
                        'transport' => 'sns',

                        'provider_event_id' =>
                            $providerEventId,

                        'topic_arn' =>
                            $snsEnvelope['TopicArn']
                            ?? null,

                        'provider_message_id' =>
                            $providerMessageId !== ''
                                ? $providerMessageId
                                : null,

                        'event_type' =>
                            $eventType !== ''
                                ? $eventType
                                : null,

                        'status' => 'received',
                        'attempts' => 0,

                        'sns_envelope' =>
                            $this->sanitizedEnvelope(
                                $snsEnvelope
                            ),

                        'provider_payload' =>
                            $providerPayload,

                        'metadata' => [
                            'signature_validated' => true,
                        ],

                        'provider_event_at' =>
                            $this->providerEventAt(
                                $providerPayload,
                                $snsEnvelope
                            ),

                        'first_received_at' => now(),
                    ]);
                } catch (Throwable $e) {
                    /*
                     * Covers a concurrent duplicate insert racing against
                     * the unique provider_event_id constraint.
                     */
                    $duplicate = EmailProviderEvent::query()
                        ->where(
                            'provider_event_id',
                            $providerEventId
                        )
                        ->first();

                    if ($duplicate !== null) {
                        return $duplicate;
                    }

                    throw $e;
                }
            }
        );
    }

    /**
     * Mark the start of a processing attempt.
     */
    public function markProcessing(
        EmailProviderEvent $event
    ): EmailProviderEvent {
        $event->forceFill([
            'status' => 'processing',
            'attempts' =>
                ((int) $event->attempts) + 1,
            'last_attempted_at' => now(),
            'last_error' => null,
        ])->save();

        return $event->refresh();
    }

    public function markProcessed(
        EmailProviderEvent $event,
        ?int $emailUsageLogId = null
    ): EmailProviderEvent {
        $event->forceFill([
            'status' => 'processed',
            'email_usage_log_id' =>
                $emailUsageLogId
                ?? $event->email_usage_log_id,
            'processed_at' => now(),
            'last_error' => null,
        ])->save();

        return $event->refresh();
    }

    public function markRetryPending(
        EmailProviderEvent $event,
        Throwable|string $error
    ): EmailProviderEvent {
        $event->forceFill([
            'status' => 'retry_pending',
            'last_error' =>
                $error instanceof Throwable
                    ? $error->getMessage()
                    : $error,
        ])->save();

        return $event->refresh();
    }

    public function markFailed(
        EmailProviderEvent $event,
        Throwable|string $error
    ): EmailProviderEvent {
        $event->forceFill([
            'status' => 'failed',
            'last_error' =>
                $error instanceof Throwable
                    ? $error->getMessage()
                    : $error,
        ])->save();

        return $event->refresh();
    }

    /**
     * We deliberately do not persist SNS Signature or SigningCertURL.
     *
     * The signature has already served its authentication purpose and
     * retaining it provides no operational value to Esubiz.
     */
    protected function sanitizedEnvelope(
        array $envelope
    ): array {
        unset(
            $envelope['Signature'],
            $envelope['SigningCertURL'],
            $envelope['SubscribeURL'],
            $envelope['UnsubscribeURL']
        );

        return $envelope;
    }

    protected function providerEventAt(
        array $payload,
        array $envelope
    ): ?string {
        $value =
            $payload['delivery']['timestamp']
            ?? $payload['bounce']['timestamp']
            ?? $payload['complaint']['timestamp']
            ?? $payload['reject']['timestamp']
            ?? $payload['mail']['timestamp']
            ?? $envelope['Timestamp']
            ?? null;

        if (!is_string($value) || trim($value) === '') {
            return null;
        }

        try {
            return \Illuminate\Support\Carbon::parse(
                $value
            )->utc()->toDateTimeString();
        } catch (Throwable) {
            return null;
        }
    }
}
