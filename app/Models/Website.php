<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Website extends Model
{
    use HasFactory;

    protected $fillable = [

        /*
        |--------------------------------------------------------------------------
        | Ownership
        |--------------------------------------------------------------------------
        */

        'owner_id',
        'developer_id',
        'plan_id',
        'workspace_id',

        /*
        |--------------------------------------------------------------------------
        | Identity
        |--------------------------------------------------------------------------
        */

        'uuid',
        'website_code',

        'name',
        'type',
        'edition',
        'owner_type',

        'slug',
        'domain',
        'subdomain',

        /*
        |--------------------------------------------------------------------------
        | Website
        |--------------------------------------------------------------------------
        */

        'industry',
        'theme',
        'template',

        'status',
        'current_step',

        'wizard_data',

        /*
        |--------------------------------------------------------------------------
        | Website Administrator
        |--------------------------------------------------------------------------
        */

        'admin_name',
        'admin_email',
        'admin_password',

        /*
        |--------------------------------------------------------------------------
        | Deployment
        |--------------------------------------------------------------------------
        */

        'deployment_progress',
        'estimated_finish_at',
        'deployment_started_at',
        'deployment_completed_at',

        'last_saved_at',

        /*
        |--------------------------------------------------------------------------
        | Limits
        |--------------------------------------------------------------------------
        */

        'multi_branch',
        'branch_limit',

        'ai_credits',
        'sms_credits',

        'storage_mb',
        'bandwidth_mb',

        /*
        |--------------------------------------------------------------------------
        | Features
        |--------------------------------------------------------------------------
        */

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

        'wizard_data' => 'array',

        'enabled_modules' => 'array',
        'enabled_features' => 'array',
        'settings' => 'array',

        'multi_branch' => 'boolean',
        'is_default' => 'boolean',
        'is_homepage' => 'boolean',
        'is_pwa' => 'boolean',
        'is_native_app' => 'boolean',

        'published_at' => 'datetime',

        'last_saved_at' => 'datetime',

        'estimated_finish_at' => 'datetime',
        'deployment_started_at' => 'datetime',
        'deployment_completed_at' => 'datetime',

    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function developer()
    {
        return $this->belongsTo(User::class, 'developer_id');
    }

    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    public function workspace()
    {
        return $this->belongsTo(Workspace::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    public function saveWizard(array $data, int $step): void
    {
        $this->update([

            'wizard_data' => array_merge(
                $this->wizard_data ?? [],
                $data
            ),

            'current_step' => $step,

            'last_saved_at' => now(),

        ]);
    }

    public function markProvisioning(): void
    {
        $this->update([

            'status' => 'provisioning',

            'deployment_progress' => 0,

            'deployment_started_at' => now(),

        ]);
    }
}
