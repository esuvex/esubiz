<?php

namespace App\Models\Aws;

use Illuminate\Database\Eloquent\Model;

class AwsServiceSetting extends Model
{
    protected $table = 'aws_service_settings';

    protected $fillable = [
        'service_key',
        'secret_payload',
        'ses_region',
        'pricing_api_region',
        'sender_address',
        'sender_name',
        'is_enabled',
        'metadata',
    ];

    protected $casts = [
        /*
         * AWS credentials are encrypted at rest using the same
         * Laravel model-encryption pattern used by Central AI.
         */
        'secret_payload' => 'encrypted:array',

        'metadata' => 'array',
        'is_enabled' => 'boolean',
    ];

    public static function central(): ?self
    {
        return static::query()
            ->where('service_key', 'central')
            ->first();
    }
}
