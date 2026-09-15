<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * ESUBIZ_EMAIL_SERVICE_SETTING_MODEL_V1
 */
class EmailServiceSetting extends Model
{
    protected $fillable = [
        'service_key',
        'name',
        'short_label',
        'description',
        'info_content',
        'daily_limit',
        'period_label',
        'is_enabled',
        'sort_order',
    ];

    protected $casts = [
        'daily_limit' => 'integer',
        'is_enabled' => 'boolean',
        'sort_order' => 'integer',
    ];
}
