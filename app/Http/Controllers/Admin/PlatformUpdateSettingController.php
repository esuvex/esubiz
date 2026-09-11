<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PlatformUpdateSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PlatformUpdateSettingController extends Controller
{
    /**
     * Save central Esubiz update lifecycle configuration.
     *
     * These values control both the current Manual workflow and,
     * later, the Automatic scheduler workflow.
     */
    public function update(Request $request): JsonResponse
    {
        $data = $request->validate([
            'mode' => [
                'required',
                Rule::in(['manual', 'automatic']),
            ],

            'default_timing' => [
                'required',
                Rule::in(['immediate', 'delayed']),
            ],

            'delay_value' => [
                'required',
                'integer',
                'min:0',
                'max:525600',
            ],

            'delay_unit' => [
                'required',
                Rule::in(['minutes', 'hours', 'days']),
            ],

            'timezone' => [
                'required',
                'string',
                'max:64',
                'timezone',
            ],

            'backup_enabled' => [
                'required',
                'boolean',
            ],

            'auto_restore_failure' => [
                'required',
                'boolean',
            ],

            'health_check_enabled' => [
                'required',
                'boolean',
            ],

            'cleanup_enabled' => [
                'required',
                'boolean',
            ],

            'success_backup_days' => [
                'required',
                'integer',
                'min:1',
                'max:3650',
            ],

            'failed_backup_days' => [
                'required',
                'integer',
                'min:1',
                'max:3650',
            ],

            /*
             * Dashboard Notices are a separate central Admin feature.
             * They are not part of the Platform Update lifecycle.
             */

            'email_notice' => [
                'required',
                'boolean',
            ],

            'notice_value' => [
                'required',
                'integer',
                'min:0',
                'max:525600',
            ],

            'notice_unit' => [
                'required',
                Rule::in(['minutes', 'hours', 'days']),
            ],

            /*
             * This is deliberately separate from mode.
             *
             * Selecting Automatic in the UI must never by itself
             * silently activate cron-driven execution.
             */
            'scheduler_enabled' => [
                'required',
                'boolean',
            ],
        ]);

        /*
         * Safety:
         *
         * The full automatic lifecycle is still being built.
         * Until it is explicitly released, Automatic mode may be
         * configured but scheduler execution remains disabled.
         */
        if (!config('esubiz.platform_updates.automatic_ready', false)) {
            $data['scheduler_enabled'] = false;
        }

        $settings = PlatformUpdateSetting::current();

        $settings->fill($data);
        $settings->save();

        return response()->json([
            'ok' => true,
            'message' => 'Update lifecycle settings saved.',
            'settings' => [
                'mode' => $settings->mode,
                'default_timing' => $settings->default_timing,
                'delay_value' => $settings->delay_value,
                'delay_unit' => $settings->delay_unit,
                'timezone' => $settings->timezone,

                'backup_enabled' => $settings->backup_enabled,
                'auto_restore_failure' => $settings->auto_restore_failure,
                'health_check_enabled' => $settings->health_check_enabled,
                'cleanup_enabled' => $settings->cleanup_enabled,

                'success_backup_days' => $settings->success_backup_days,
                'failed_backup_days' => $settings->failed_backup_days,

                'email_notice' => $settings->email_notice,
                'notice_value' => $settings->notice_value,
                'notice_unit' => $settings->notice_unit,

                'scheduler_enabled' => $settings->scheduler_enabled,
                'automatic_ready' => (bool) config(
                    'esubiz.platform_updates.automatic_ready',
                    false
                ),
            ],
        ]);
    }
}
