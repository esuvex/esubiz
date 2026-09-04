<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EsubizSsoSettingsController extends Controller
{
    /*
     * ESUBIZ_CENTRAL_SSO_CONTROLS_V1
     *
     * Central policy controls only.
     * No OAuth credentials are exposed or stored here.
     */
    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'enabled' => ['nullable', 'boolean'],
            'core_enabled' => ['nullable', 'boolean'],
            'external_enabled' => ['nullable', 'boolean'],
        ]);

        $settings = [
            'auth.esubiz_sso.enabled' =>
                $request->boolean('enabled') ? '1' : '0',

            'auth.esubiz_sso.core_enabled' =>
                $request->boolean('core_enabled') ? '1' : '0',

            'auth.esubiz_sso.external_enabled' =>
                $request->boolean('external_enabled') ? '1' : '0',
        ];

        foreach ($settings as $key => $value) {
            DB::table('site_settings')->updateOrInsert(
                [
                    'workspace_id' => null,
                    'key' => $key,
                ],
                [
                    'value' => $value,
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }

        return redirect()
            ->route(
                'admin.site-settings.index',
                [
                    'tab' => 'auth',
                    'subtab' => 'esubiz',
                ]
            )
            ->with(
                'success',
                'Esubiz SSO settings updated successfully.'
            );
    }
}
