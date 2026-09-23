<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * ESUBIZ_EMAIL_PROVIDER_EVENTS_V1
 *
 * Durable provider webhook/event inbox.
 *
 * This table is deliberately separate from email_usage_logs:
 *
 * - email_usage_logs owns Esubiz send/billing lifecycle;
 * - email_provider_events owns externally delivered provider events.
 *
 * SNS MessageId is the transport-level idempotency key.
 * provider_message_id correlates the event back to SES/email_usage_logs.
 *
 * The raw authenticated provider payload is retained for controlled
 * processing/audit/retry. It must never contain Esubiz AWS credentials.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('email_provider_events')) {
            return;
        }

        Schema::create(
            'email_provider_events',
            function (Blueprint $table) {
                $table->id();

                /*
                 * Transport/provider identity.
                 */
                $table
                    ->string('provider', 50)
                    ->default('aws')
                    ->index();

                $table
                    ->string('provider_service', 50)
                    ->default('ses')
                    ->index();

                $table
                    ->string('transport', 50)
                    ->default('sns')
                    ->index();

                /*
                 * Amazon SNS MessageId.
                 *
                 * This is globally unique for the SNS delivery and is our
                 * durable webhook idempotency key.
                 */
                $table
                    ->string('provider_event_id', 191)
                    ->unique();

                $table
                    ->string('topic_arn', 512)
                    ->nullable();

                /*
                 * SES mail.messageId used to correlate with
                 * email_usage_logs.provider_message_id.
                 */
                $table
                    ->string('provider_message_id', 255)
                    ->nullable()
                    ->index();

                /*
                 * SES notificationType/eventType:
                 * Delivery, Bounce, Complaint, Reject, etc.
                 */
                $table
                    ->string('event_type', 80)
                    ->nullable()
                    ->index();

                /*
                 * Processing lifecycle:
                 *
                 * received
                 * processing
                 * processed
                 * retry_pending
                 * failed
                 */
                $table
                    ->string('status', 40)
                    ->default('received')
                    ->index();

                /*
                 * Optional correlation to the Esubiz send lifecycle.
                 *
                 * No foreign key is used intentionally so provider-event
                 * ingestion remains resilient during deployment/migration
                 * sequencing and historical retention.
                 */
                $table
                    ->unsignedBigInteger('email_usage_log_id')
                    ->nullable()
                    ->index();

                /*
                 * Number of processing attempts made by Esubiz after the
                 * authenticated event was durably accepted.
                 */
                $table
                    ->unsignedInteger('attempts')
                    ->default(0);

                /*
                 * Authenticated SNS envelope and decoded SES payload.
                 *
                 * These are provider event data only. Never store AWS
                 * access keys, secret keys or application credentials here.
                 */
                $table
                    ->json('sns_envelope')
                    ->nullable();

                $table
                    ->json('provider_payload')
                    ->nullable();

                /*
                 * Small normalized provider metadata useful for operational
                 * inspection without reparsing the full payload.
                 */
                $table
                    ->json('metadata')
                    ->nullable();

                $table
                    ->text('last_error')
                    ->nullable();

                $table
                    ->timestamp('provider_event_at')
                    ->nullable()
                    ->index();

                $table
                    ->timestamp('first_received_at')
                    ->nullable();

                $table
                    ->timestamp('last_attempted_at')
                    ->nullable();

                $table
                    ->timestamp('processed_at')
                    ->nullable();

                $table->timestamps();

                $table->index(
                    [
                        'provider',
                        'provider_service',
                        'status',
                    ],
                    'email_provider_events_provider_status_idx'
                );

                $table->index(
                    [
                        'provider_message_id',
                        'event_type',
                    ],
                    'email_provider_events_message_type_idx'
                );
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'email_provider_events'
        );
    }
};
