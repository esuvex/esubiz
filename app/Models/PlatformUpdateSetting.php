<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlatformUpdateSetting extends Model
{
    protected $table = 'platform_update_settings';

    protected $guarded = [];

    protected $casts = [
        'delay_value' => 'integer',

        'backup_enabled' => 'boolean',
        'auto_restore_failure' => 'boolean',
        'health_check_enabled' => 'boolean',
        'cleanup_enabled' => 'boolean',

        'success_backup_days' => 'integer',
        'failed_backup_days' => 'integer',

        'dashboard_notice' => 'boolean',
        'email_notice' => 'boolean',
        'notice_value' => 'integer',

        'scheduler_enabled' => 'boolean',

        'config' => 'array',
    ];

    /**
     * Central authority for Esubiz update lifecycle configuration.
     *
     * There should normally be one global record (ID 1).
     */
    public static function current(): self
    {
        return static::query()->firstOrCreate(
            ['id' => 1],
            [
                'mode' => 'manual',
                'default_timing' => 'immediate',
                'delay_value' => 0,
                'delay_unit' => 'minutes',
                'timezone' => 'Africa/Lagos',

                'backup_enabled' => true,
                'auto_restore_failure' => true,
                'health_check_enabled' => true,
                'cleanup_enabled' => true,

                'success_backup_days' => 14,
                'failed_backup_days' => 30,

                'dashboard_notice' => true,
                'email_notice' => true,
                'notice_value' => 24,
                'notice_unit' => 'hours',

                /*
                 * Never silently activate automatic execution.
                 */
                'scheduler_enabled' => false,

                'config' => null,
            ]
        );
    }

    public function isManual(): bool
    {
        return $this->mode === 'manual';
    }

    public function isAutomatic(): bool
    {
        return $this->mode === 'automatic';
    }

    public function automaticExecutionEnabled(): bool
    {
        return $this->isAutomatic()
            && $this->scheduler_enabled;
    }
}
