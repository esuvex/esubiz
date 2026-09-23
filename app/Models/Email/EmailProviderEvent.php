<?php

namespace App\Models\Email;

use Illuminate\Database\Eloquent\Model;

/**
 * ESUBIZ_EMAIL_PROVIDER_EVENT_MODEL_V1
 *
 * Durable authenticated provider-event inbox record.
 */
class EmailProviderEvent extends Model
{
    protected $table = 'email_provider_events';

    protected $fillable = [
        'provider',
        'provider_service',
        'transport',
        'provider_event_id',
        'topic_arn',
        'provider_message_id',
        'event_type',
        'status',
        'email_usage_log_id',
        'attempts',
        'sns_envelope',
        'provider_payload',
        'metadata',
        'last_error',
        'provider_event_at',
        'first_received_at',
        'last_attempted_at',
        'processed_at',
    ];

    protected $casts = [
        'attempts' => 'integer',
        'sns_envelope' => 'array',
        'provider_payload' => 'array',
        'metadata' => 'array',
        'provider_event_at' => 'datetime',
        'first_received_at' => 'datetime',
        'last_attempted_at' => 'datetime',
        'processed_at' => 'datetime',
    ];
}
