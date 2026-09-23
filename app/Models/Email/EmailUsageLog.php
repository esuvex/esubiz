<?php

namespace App\Models\Email;

use Illuminate\Database\Eloquent\Model;

class EmailUsageLog extends Model
{
    protected $table = 'email_usage_logs';

    protected $fillable = [
        'request_uuid',
        'website_id',
        'user_id',
        'deployment_scope',
        'sender_mode',
        'email_type',
        'provider',
        'provider_service',
        'provider_region',
        'provider_message_id',
        'status',
        'recipient_count',
        'provider_unit_cost',
        'provider_total_cost',
        'provider_currency',
        'email_markup_type',
        'email_markup_value',
        'email_markup_amount',
        'selling_cost_provider_currency',
        'exchange_rate',
        'exchange_rate_provider',
        'exchange_rate_date',
        'central_currency',
        'selling_cost_central_currency',
        'uncapped_rate_per_recipient_base_currency',
        'max_rate_per_recipient_base_currency',
        'cap_applied',
        'selling_rate_per_recipient_base_currency',
        'email_credit_value',
        'credits_quoted',
        'credits_charged',
        'credit_transaction_reference',
        'source_type',
        'source_id',
        'metadata',
        'error_message',
        'provider_accepted_at',
        'completed_at',
    ];

    protected $casts = [
        'website_id' => 'integer',
        'user_id' => 'integer',
        'recipient_count' => 'integer',

        'provider_unit_cost' =>
            'decimal:12',

        'provider_total_cost' =>
            'decimal:12',

        'email_markup_value' =>
            'decimal:8',

        'email_markup_amount' =>
            'decimal:12',

        'selling_cost_provider_currency' =>
            'decimal:12',

        'exchange_rate' =>
            'decimal:12',

        'exchange_rate_date' =>
            'date',

        'selling_cost_central_currency' =>
            'decimal:8',

        'uncapped_rate_per_recipient_base_currency' =>
            'decimal:8',

        'max_rate_per_recipient_base_currency' =>
            'decimal:8',

        'cap_applied' =>
            'boolean',

        'selling_rate_per_recipient_base_currency' =>
            'decimal:8',

        'email_credit_value' =>
            'decimal:8',

        'credits_quoted' =>
            'decimal:6',

        'credits_charged' =>
            'decimal:6',

        'metadata' =>
            'array',

        'provider_accepted_at' =>
            'datetime',

        'completed_at' =>
            'datetime',
    ];
}
