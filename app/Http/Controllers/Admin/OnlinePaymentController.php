<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OnlinePaymentController extends Controller
{

    public function gateways()
    {
        return view('admin.payment-gateways.index');
    }

    public function index()
    {
        $gateways = DB::table('payment_providers')
            ->whereNull('deleted_at')
            ->orderBy('priority')
            ->orderBy('name')
            ->get();

        return view('admin.online-payment.index', compact('gateways'));
    }

    public function update(Request $request, int $provider)
    {
        $gateway = DB::table('payment_providers')
            ->where('id', $provider)
            ->whereNull('deleted_at')
            ->first();

        abort_unless($gateway, 404);

        $data = $request->validate([
            'usage_contexts' => [
                'nullable',
                'array',
            ],

            'usage_contexts.*' => [
                'string',
                'in:user_marketplace_checkout,user_wallet_funding,user_checkout_link,developer_marketplace_checkout,developer_wallet_funding,developer_checkout_link',
            ],

            'conversion_type' => [
                'nullable',
                'in:none,fiat,crypto',
            ],

            'conversion_provider' => [
                'nullable',
                'in:frankfurter,coingecko,manual',
            ],

            'conversion_target' => [
                'nullable',
                'string',
                'max:30',
            ],

            'manual_conversion_rate' => [
                'nullable',
                'numeric',
                'gt:0',
            ],

            'markup_type' => [
                'nullable',
                'in:percentage,fixed',
            ],

            'markup_value' => [
                'nullable',
                'numeric',
                'min:0',
            ],
        ]);

        $credentials = $request->input('credentials', []);
        $settings = $request->input('settings', []);

        $existingCredentials = $gateway->credentials
            ? json_decode($gateway->credentials, true)
            : [];

        $existingSettings = $gateway->settings
            ? json_decode($gateway->settings, true)
            : [];

        foreach ($credentials as $key => $value) {
            if ($value === null || $value === '') {
                unset($credentials[$key]);
                continue;
            }

            /*
             * Password/secret fields are displayed masked in the
             * admin UI. Never save the mask over the real credential.
             */
            if ((string) $value === '••••••••') {
                unset($credentials[$key]);
                continue;
            }

            $credentials[$key] = trim((string) $value);
        }

        foreach ($settings as $key => $value) {
            $settings[$key] = is_string($value)
                ? trim($value)
                : $value;
        }

        $mergedCredentials = array_merge(
            $existingCredentials,
            $credentials
        );

        $mergedSettings = array_merge(
            $existingSettings,
            $settings
        );

        DB::table('payment_providers')
            ->where('id', $gateway->id)
            ->update([
                'credentials' =>
                    json_encode($mergedCredentials),

                'settings' =>
                    json_encode($mergedSettings),

                /*
                 * Allowed payment contexts.
                 *
                 * An explicit empty array means Admin has disabled
                 * this gateway for every payment use.
                 */
                'usage_contexts' => json_encode(
                    array_values(
                        array_intersect(
                            $data['usage_contexts'] ?? [],
                            [
                                'user_marketplace_checkout',
                                'user_wallet_funding',
                                'user_checkout_link',

                                'developer_marketplace_checkout',
                                'developer_wallet_funding',
                                'developer_checkout_link',
                            ]
                        )
                    )
                ),

                /*
                 * Currency / crypto conversion.
                 */
                'conversion_enabled' =>
                    $request->boolean(
                        'conversion_enabled'
                    ),

                'conversion_type' =>
                    $request->boolean(
                        'conversion_enabled'
                    )
                        ? (
                            $data['conversion_type']
                                ?? 'none'
                        )
                        : 'none',

                'conversion_provider' =>
                    $request->boolean(
                        'conversion_enabled'
                    )
                        ? (
                            $data['conversion_provider']
                                ?? null
                        )
                        : null,

                'conversion_target' =>
                    $request->boolean(
                        'conversion_enabled'
                    )
                        ? (
                            !empty(
                                $data['conversion_target']
                            )
                                ? strtoupper(
                                    trim(
                                        $data[
                                            'conversion_target'
                                        ]
                                    )
                                )
                                : null
                        )
                        : null,

                'manual_conversion_rate' =>
                    (
                        $request->boolean(
                            'conversion_enabled'
                        )
                        && (
                            $data['conversion_provider']
                                ?? null
                        ) === 'manual'
                    )
                        ? (
                            isset(
                                $data[
                                    'manual_conversion_rate'
                                ]
                            )
                            && $data[
                                'manual_conversion_rate'
                            ] !== ''
                                ? (float) $data[
                                    'manual_conversion_rate'
                                ]
                                : null
                        )
                        : null,

                /*
                 * Markup is applied AFTER conversion.
                 */
                'markup_enabled' =>
                    $request->boolean(
                        'markup_enabled'
                    ),

                'markup_type' =>
                    $data['markup_type']
                        ?? 'percentage',

                'markup_value' =>
                    max(
                        0,
                        (float) (
                            $data['markup_value']
                                ?? 0
                        )
                    ),

                'is_active' =>
                    $request->boolean('is_active'),

                'updated_at' => now(),
            ]);

        return redirect()
            ->route('admin.payment-gateways.online.index')
            ->with('success', "{$gateway->name} settings saved successfully.");
    }
}
