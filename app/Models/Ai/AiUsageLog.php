<?php

namespace App\Models\Ai;

use Illuminate\Database\Eloquent\Model;

class AiUsageLog extends Model
{
    protected $connection = 'mysql';

    protected $table =
        'ai_usage_logs';


    protected $fillable = [
        'request_uuid',
        'route_key',
        'ai_provider_id',
        'ai_model_id',
        'user_id',
        'website_id',
        'provider_request_id',
        'status',
        'used_fallback',
        'input_tokens',
        'cached_input_tokens',
        'output_tokens',
        'total_tokens',
        'provider_cost_usd',
        'credits_charged',
        'error_message',
        'metadata',
        'completed_at',
    ];


    protected $casts = [
        'used_fallback' =>
            'boolean',

        'input_tokens' =>
            'integer',

        'cached_input_tokens' =>
            'integer',

        'output_tokens' =>
            'integer',

        'total_tokens' =>
            'integer',

        'provider_cost_usd' =>
            'decimal:8',

        'credits_charged' =>
            'decimal:6',

        'metadata' =>
            'array',

        'completed_at' =>
            'datetime',
    ];
}
