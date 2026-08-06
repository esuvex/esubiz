<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Website extends Model
{
    use HasFactory;

    protected $fillable = [
        'owner_id',
        'plan_id',
        'workspace_id',

        'uuid',
        'website_code',

        'name',
        'type',
        'edition',
        'owner_type',

        'slug',
        'domain',
        'subdomain',

        'industry',
        'theme',
        'template',

        'status',

        'multi_branch',
        'branch_limit',

        'ai_credits',
        'sms_credits',

        'storage_mb',
        'bandwidth_mb',

        'enabled_modules',
        'enabled_features',

        'settings',

        'is_default',
        'is_homepage',
        'is_pwa',
        'is_native_app',

        'published_at',
    ];

    protected $casts = [
        'enabled_modules' => 'array',
        'enabled_features' => 'array',
        'settings' => 'array',

        'multi_branch' => 'boolean',
        'is_default' => 'boolean',
        'is_homepage' => 'boolean',
        'is_pwa' => 'boolean',
        'is_native_app' => 'boolean',

        'published_at' => 'datetime',
    ];

    /**
     * Website owner.
     */
    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /**
     * Website plan.
     */
    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    /**
     * Workspace.
     */
    public function workspace()
    {
        return $this->belongsTo(Workspace::class);
    }
}
