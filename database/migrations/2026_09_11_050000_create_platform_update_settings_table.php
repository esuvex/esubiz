<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('platform_update_settings')) {
            Schema::create(
                'platform_update_settings',
                function (Blueprint $table) {
                    $table->id();

                    /*
                     * Update operation mode.
                     *
                     * manual:
                     * Admin explicitly performs Dry Run / Run Update.
                     *
                     * automatic:
                     * Scheduler performs the exact same controlled
                     * update pipeline according to Admin configuration.
                     */
                    $table->string('mode', 20)
                        ->default('manual');

                    /*
                     * Global default execution timing.
                     *
                     * immediate
                     * delayed
                     */
                    $table->string('default_timing', 20)
                        ->default('immediate');

                    $table->unsignedInteger('delay_value')
                        ->default(0);

                    /*
                     * minutes
                     * hours
                     * days
                     */
                    $table->string('delay_unit', 10)
                        ->default('minutes');

                    /*
                     * Scheduler timezone.
                     */
                    $table->string('timezone', 64)
                        ->default('Africa/Lagos');

                    /*
                     * Safety lifecycle defaults.
                     */
                    $table->boolean('backup_enabled')
                        ->default(true);

                    $table->boolean('auto_restore_failure')
                        ->default(true);

                    $table->boolean('health_check_enabled')
                        ->default(true);

                    $table->boolean('cleanup_enabled')
                        ->default(true);

                    /*
                     * Successful update checkpoints can expire after
                     * this configurable number of days.
                     */
                    $table->unsignedSmallInteger('success_backup_days')
                        ->default(14);

                    /*
                     * Failed/restored checkpoints are intentionally
                     * retained longer by default.
                     */
                    $table->unsignedSmallInteger('failed_backup_days')
                        ->default(30);

                    /*
                     * Tenant communication defaults.
                     */
                    $table->boolean('dashboard_notice')
                        ->default(true);

                    $table->boolean('email_notice')
                        ->default(true);

                    $table->unsignedInteger('notice_value')
                        ->default(24);

                    $table->string('notice_unit', 10)
                        ->default('hours');

                    /*
                     * When enabled later, the scheduler/cron worker
                     * may process due automatic updates.
                     *
                     * This remains disabled initially even if other
                     * automatic settings exist.
                     */
                    $table->boolean('scheduler_enabled')
                        ->default(false);

                    /*
                     * Optional Admin notes / future extensibility.
                     */
                    $table->json('config')
                        ->nullable();

                    $table->timestamps();
                }
            );
        }

        /*
         * Seed one editable central configuration record.
         *
         * These are defaults only. Admin UI will become the authority.
         */
        DB::table('platform_update_settings')
            ->updateOrInsert(
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
                     * Automatic processing is deliberately OFF
                     * until the full lifecycle is built and verified.
                     */
                    'scheduler_enabled' => false,

                    'config' => null,

                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
    }

    public function down(): void
    {
        Schema::dropIfExists('platform_update_settings');
    }
};
