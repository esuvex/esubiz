<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WebsiteWizardSettingsController extends Controller
{
    /*
     * ESUBIZ_WEBSITE_WIZARD_SETTINGS_CONTROLLER_V1
     *
     * Presentation settings only.
     * Backend deployment/compilation must never wait on these values.
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            /*
             * ESUBIZ_SEPARATE_WIZARD_PROGRESS_TIMERS_V1
             *
             * Presentation-only timers. They never control the actual
             * deployment or compilation process.
             */
            'user_progress_minimum_seconds' =>
                ['required', 'integer', 'min:0', 'max:60'],

            'developer_progress_minimum_seconds' =>
                ['required', 'integer', 'min:0', 'max:60'],

            'user_progress_title' =>
                ['required', 'string', 'max:120'],
            /*
             * ESUBIZ_WIZARD_REAL_PROGRESS_CONFIG_V1
             *
             * These texts accompany REAL backend progress percentages.
             * They do not generate or simulate progress.
             */
            'user_progress_text_0_19' =>
                ['required', 'string', 'max:500'],
            'user_progress_text_20_39' =>
                ['required', 'string', 'max:500'],
            'user_progress_text_40_59' =>
                ['required', 'string', 'max:500'],
            'user_progress_text_60_79' =>
                ['required', 'string', 'max:500'],
            'user_progress_text_80_99' =>
                ['required', 'string', 'max:500'],
            'user_progress_text_100' =>
                ['required', 'string', 'max:500'],
            'user_success_title' =>
                ['required', 'string', 'max:120'],
            'user_success_text' =>
                ['required', 'string', 'max:500'],
            'user_failure_title' =>
                ['required', 'string', 'max:120'],
            'user_failure_text' =>
                ['required', 'string', 'max:500'],

            'developer_progress_title' =>
                ['required', 'string', 'max:120'],
            'developer_progress_text_0_19' =>
                ['required', 'string', 'max:500'],
            'developer_progress_text_20_39' =>
                ['required', 'string', 'max:500'],
            'developer_progress_text_40_59' =>
                ['required', 'string', 'max:500'],
            'developer_progress_text_60_79' =>
                ['required', 'string', 'max:500'],
            'developer_progress_text_80_99' =>
                ['required', 'string', 'max:500'],
            'developer_progress_text_100' =>
                ['required', 'string', 'max:500'],
            'developer_success_title' =>
                ['required', 'string', 'max:120'],
            'developer_success_text' =>
                ['required', 'string', 'max:500'],
            'developer_failure_title' =>
                ['required', 'string', 'max:120'],
            'developer_failure_text' =>
                ['required', 'string', 'max:500'],

            'developer_payment_label' =>
                ['required', 'string', 'max:60'],
            'developer_recompile_label' =>
                ['required', 'string', 'max:60'],
            'developer_dashboard_label' =>
                ['required', 'string', 'max:60'],
        ]);

        $settings = [
            'wizard.user.progress_minimum_seconds' =>
                (string) $validated['user_progress_minimum_seconds'],

            'wizard.developer.progress_minimum_seconds' =>
                (string) $validated['developer_progress_minimum_seconds'],

            'wizard.user.progress_title' =>
                $validated['user_progress_title'],
            'wizard.user.progress_text.0_19' =>
                $validated['user_progress_text_0_19'],
            'wizard.user.progress_text.20_39' =>
                $validated['user_progress_text_20_39'],
            'wizard.user.progress_text.40_59' =>
                $validated['user_progress_text_40_59'],
            'wizard.user.progress_text.60_79' =>
                $validated['user_progress_text_60_79'],
            'wizard.user.progress_text.80_99' =>
                $validated['user_progress_text_80_99'],
            'wizard.user.progress_text.100' =>
                $validated['user_progress_text_100'],
            'wizard.user.success_title' =>
                $validated['user_success_title'],
            'wizard.user.success_text' =>
                $validated['user_success_text'],
            'wizard.user.failure_title' =>
                $validated['user_failure_title'],
            'wizard.user.failure_text' =>
                $validated['user_failure_text'],

            'wizard.developer.progress_title' =>
                $validated['developer_progress_title'],
            'wizard.developer.progress_text.0_19' =>
                $validated['developer_progress_text_0_19'],
            'wizard.developer.progress_text.20_39' =>
                $validated['developer_progress_text_20_39'],
            'wizard.developer.progress_text.40_59' =>
                $validated['developer_progress_text_40_59'],
            'wizard.developer.progress_text.60_79' =>
                $validated['developer_progress_text_60_79'],
            'wizard.developer.progress_text.80_99' =>
                $validated['developer_progress_text_80_99'],
            'wizard.developer.progress_text.100' =>
                $validated['developer_progress_text_100'],
            'wizard.developer.success_title' =>
                $validated['developer_success_title'],
            'wizard.developer.success_text' =>
                $validated['developer_success_text'],
            'wizard.developer.failure_title' =>
                $validated['developer_failure_title'],
            'wizard.developer.failure_text' =>
                $validated['developer_failure_text'],

            'wizard.developer.payment_label' =>
                $validated['developer_payment_label'],
            'wizard.developer.recompile_label' =>
                $validated['developer_recompile_label'],
            'wizard.developer.dashboard_label' =>
                $validated['developer_dashboard_label'],
        ];

        DB::transaction(function () use ($settings) {
            foreach ($settings as $key => $value) {
                DB::table('site_settings')->updateOrInsert(
                    [
                        'workspace_id' => null,
                        'key' => $key,
                    ],
                    [
                        'value' => $value,
                        'updated_at' => now(),
                    ]
                );
            }
        });

        return redirect()
            ->route('admin.site-settings.index', [
                'tab' => 'website-wizard',
            ])
            ->with(
                'success',
                'Website Wizard settings updated successfully.'
            );
    }
}
