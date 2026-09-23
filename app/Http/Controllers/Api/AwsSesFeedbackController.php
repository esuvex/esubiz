<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Email\EmailProviderEventInboxService;
use App\Services\Email\SesFeedbackEventService;
use Aws\Sns\Message;
use Aws\Sns\MessageValidator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use JsonException;
use RuntimeException;
use Throwable;

/**
 * ESUBIZ_AWS_SES_SNS_RECEIVER_V2_DURABLE_EVENTS
 *
 * Central-only authenticated Amazon SNS receiver for SES outbound feedback.
 *
 * Security:
 * - validates SNS cryptographic signatures using the official AWS validator;
 * - never trusts tenant/user supplied identity;
 * - only accepts SNS Notification and SubscriptionConfirmation messages;
 * - the route is bound to the Central esubiz.com host;
 * - CSRF is disabled only on the exact server-to-server route.
 */
class AwsSesFeedbackController extends Controller
{
    public function __invoke(
        Request $request,
        SesFeedbackEventService $feedback,
        EmailProviderEventInboxService $inbox
    ): JsonResponse {
        try {
            $sns = Message::fromJsonString($request->getContent());
        } catch (Throwable $e) {
            Log::warning('Rejected malformed AWS SNS request.', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'ok' => false,
                'message' => 'Invalid SNS message.',
            ], 400);
        }

        try {
            (new MessageValidator())->validate($sns);
        } catch (Throwable $e) {
            Log::warning('Rejected AWS SNS request with invalid signature.', [
                'type' => $sns['Type'] ?? null,
                'message_id' => $sns['MessageId'] ?? null,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'ok' => false,
                'message' => 'Invalid SNS signature.',
            ], 403);
        }

        $type = trim((string) ($sns['Type'] ?? ''));

        if ($type === 'SubscriptionConfirmation') {
            return $this->confirmSubscription($sns);
        }

        if ($type !== 'Notification') {
            Log::warning('Rejected unsupported AWS SNS message type.', [
                'type' => $type,
                'message_id' => $sns['MessageId'] ?? null,
            ]);

            return response()->json([
                'ok' => false,
                'message' => 'Unsupported SNS message type.',
            ], 400);
        }

        try {
            $payload = json_decode(
                (string) ($sns['Message'] ?? ''),
                true,
                512,
                JSON_THROW_ON_ERROR
            );
        } catch (JsonException $e) {
            Log::warning('Rejected SNS notification with invalid SES payload.', [
                'message_id' => $sns['MessageId'] ?? null,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'ok' => false,
                'message' => 'Invalid SES notification payload.',
            ], 400);
        }

        if (!is_array($payload)) {
            return response()->json([
                'ok' => false,
                'message' => 'Invalid SES notification payload.',
            ], 400);
        }

        /*
         * Until the pending provider-event migration is deliberately run,
         * preserve the already-working authenticated direct processing path.
         *
         * Once the table exists, every authenticated SNS Notification is
         * durably recorded before SES feedback processing begins.
         */
        if (!$inbox->available()) {
            try {
                $result = $feedback->handle($payload);
            } catch (RuntimeException $e) {
                Log::warning('SES feedback processing is retryable.', [
                    'sns_message_id' => $sns['MessageId'] ?? null,
                    'error' => $e->getMessage(),
                ]);

                return response()->json([
                    'ok' => false,
                    'retryable' => true,
                    'message' => 'SES feedback cannot yet be processed.',
                ], 503);
            } catch (Throwable $e) {
                report($e);

                return response()->json([
                    'ok' => false,
                    'retryable' => true,
                    'message' => 'SES feedback processing failed.',
                ], 503);
            }

            return response()->json([
                'ok' => true,
                'result' => $result,
                'durable_event_inbox' => false,
            ]);
        }

        try {
            /*
             * Signature validation has already succeeded above. Only
             * authenticated SNS envelopes may enter the durable inbox.
             */
            $providerEvent = $inbox->receive(
                $sns->toArray(),
                $payload
            );

            /*
             * SNS may redeliver the same MessageId. Once our durable event
             * has completed, acknowledge it without touching the usage log
             * or billing lifecycle again.
             */
            if ($providerEvent->status === 'processed') {
                return response()->json([
                    'ok' => true,
                    'already_processed' => true,
                    'provider_event_id' => $providerEvent->provider_event_id,
                    'email_usage_log_id' => $providerEvent->email_usage_log_id,
                ]);
            }

            $providerEvent = $inbox->markProcessing($providerEvent);

            $result = $feedback->handle($payload);

            $providerEvent = $inbox->markProcessed(
                $providerEvent,
                isset($result['email_usage_log_id'])
                    ? (int) $result['email_usage_log_id']
                    : null
            );
        } catch (RuntimeException $e) {
            if (isset($providerEvent)) {
                try {
                    $inbox->markRetryPending($providerEvent, $e);
                } catch (Throwable $markingError) {
                    report($markingError);
                }
            }

            Log::warning('SES feedback processing is retryable.', [
                'sns_message_id' => $sns['MessageId'] ?? null,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'ok' => false,
                'retryable' => true,
                'message' => 'SES feedback cannot yet be processed.',
            ], 503);
        } catch (Throwable $e) {
            if (isset($providerEvent)) {
                try {
                    $inbox->markRetryPending($providerEvent, $e);
                } catch (Throwable $markingError) {
                    report($markingError);
                }
            }

            report($e);

            return response()->json([
                'ok' => false,
                'retryable' => true,
                'message' => 'SES feedback processing failed.',
            ], 503);
        }

        return response()->json([
            'ok' => true,
            'result' => $result,
            'provider_event_id' => $providerEvent->provider_event_id,
            'durable_event_inbox' => true,
        ]);
    }

    private function confirmSubscription(Message $sns): JsonResponse
    {
        $subscribeUrl = trim(
            (string) ($sns['SubscribeURL'] ?? '')
        );

        if ($subscribeUrl === '') {
            return response()->json([
                'ok' => false,
                'message' => 'SNS subscription URL is missing.',
            ], 400);
        }

        $parts = parse_url($subscribeUrl);

        if (
            !is_array($parts)
            || strtolower((string) ($parts['scheme'] ?? '')) !== 'https'
            || !$this->isTrustedSnsHost(
                strtolower((string) ($parts['host'] ?? ''))
            )
        ) {
            Log::warning('Rejected untrusted SNS subscription URL.', [
                'host' => $parts['host'] ?? null,
            ]);

            return response()->json([
                'ok' => false,
                'message' => 'Untrusted SNS subscription URL.',
            ], 400);
        }

        try {
            $response = Http::timeout(10)
                ->connectTimeout(5)
                ->get($subscribeUrl);

            if (!$response->successful()) {
                throw new RuntimeException(
                    'SNS subscription confirmation returned HTTP '
                    . $response->status()
                    . '.'
                );
            }
        } catch (Throwable $e) {
            Log::warning('SNS subscription confirmation failed.', [
                'message_id' => $sns['MessageId'] ?? null,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'ok' => false,
                'retryable' => true,
                'message' => 'SNS subscription confirmation failed.',
            ], 503);
        }

        Log::info('AWS SNS subscription confirmed.', [
            'message_id' => $sns['MessageId'] ?? null,
            'topic_arn' => $sns['TopicArn'] ?? null,
        ]);

        return response()->json([
            'ok' => true,
            'subscription_confirmed' => true,
        ]);
    }

    private function isTrustedSnsHost(string $host): bool
    {
        if ($host === '') {
            return false;
        }

        /*
         * AWS SNS confirmation URLs use regional sns.<region>.amazonaws.com
         * hosts. Also permit the AWS China suffix without allowing arbitrary
         * amazonaws lookalike domains.
         */
        return (bool) preg_match(
            '/^sns\.[a-z0-9-]+\.amazonaws\.com(?:\.cn)?$/',
            $host
        );
    }
}
