<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DeveloperBuild extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'uuid',
        'developer_id',
        'project_name',
        'build_id',
        'version',
        'website_type',
        'addon_bundle',
        'theme',
        'modules',
        'configuration',
        'selected_addons',
        'selected_modules',
        'selected_theme',
        'ai_theme_enabled',
        'ai_theme_prompt',
        'ai_theme_preferences',
        'ai_theme_status',
        'ai_theme_reference',
        'status',
        'stage',
        'build_type',
        'files_count',
        'package_size',
        'subtotal',
        'total_cost',
        'payment_status',
        'license_registration_id',
        'license_key',
        'paid_at',
        'package_reference',
        'download_url',
        'error_message',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'modules' => 'array',
        'configuration' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'paid_at' => 'datetime',
        'subtotal' => 'decimal:2',
        'total_cost' => 'decimal:2',
        'ai_theme_enabled' => 'boolean',
        'ai_theme_preferences' => 'array',
        'ai_theme_reference' => 'array',
    ];
}
