<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Platform\CentralSiteSettingsService;
use DateTimeZone;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CentralSiteSettingsController extends Controller
{
    /*
     * ESUBIZ_CENTRAL_MAIN_SITE_SETTINGS_PREMIUM_V1
     *
     * Each Main Settings section saves independently.
     *
     * Central remains authoritative for Central, SaaS and
     * off-server consumers.
     */
    public function update(
        Request $request,
        CentralSiteSettingsService $settings
    ): RedirectResponse {
        $section = (string) $request->input(
            'section',
            'general'
        );

        if (
            !in_array(
                $section,
                [
                    'general',
                    'branding',
                    'regional',
                    'seo',
                    'system',
                ],
                true
            )
        ) {
            abort(422, 'Invalid settings section.');
        }

        if ($section === 'general') {
            $validated = $request->validate([
                'site_name' => [
                    'required',
                    'string',
                    'max:120',
                ],

                'business_email' => [
                    'nullable',
                    'email',
                    'max:190',
                ],

                'support_email' => [
                    'nullable',
                    'email',
                    'max:190',
                ],

                'phone' => [
                    'nullable',
                    'string',
                    'max:60',
                ],

                'address' => [
                    'nullable',
                    'string',
                    'max:1000',
                ],

                'registration_number' => [
                    'nullable',
                    'string',
                    'max:100',
                ],
            ]);

            $settings->setMany([
                'platform.site_name' =>
                    trim($validated['site_name']),

                'platform.business_email' =>
                    trim(
                        (string) (
                            $validated['business_email']
                            ?? ''
                        )
                    ),

                'platform.support_email' =>
                    trim(
                        (string) (
                            $validated['support_email']
                            ?? ''
                        )
                    ),

                'platform.phone' =>
                    trim(
                        (string) (
                            $validated['phone']
                            ?? ''
                        )
                    ),

                'platform.address' =>
                    trim(
                        (string) (
                            $validated['address']
                            ?? ''
                        )
                    ),

                'platform.registration_number' =>
                    trim(
                        (string) (
                            $validated['registration_number']
                            ?? ''
                        )
                    ),
            ]);
        }

        if ($section === 'branding') {
            $request->validate([
                /*
                 * Recommended:
                 * Logo    180 x 60
                 * Favicon 64 x 64 or 128 x 128
                 *
                 * Exact dimensions are intentionally not forced.
                 */
                'logo' => [
                    'nullable',
                    'file',
                    'mimes:png,jpg,jpeg,webp,svg',
                    'max:4096',
                ],

                'favicon' => [
                    'nullable',
                    'file',
                    'mimes:png,jpg,jpeg,webp,svg,ico',
                    'max:2048',
                ],
            ]);

            if ($request->hasFile('logo')) {
                $settings->replaceLogo(
                    $request->file('logo')
                );
            }

            if ($request->hasFile('favicon')) {
                $settings->replaceFavicon(
                    $request->file('favicon')
                );
            }
        }

        if ($section === 'regional') {
            $validated = $request->validate([
                'timezone' => [
                    'required',
                    'string',
                    Rule::in(
                        DateTimeZone::listIdentifiers()
                    ),
                ],

                'country' => [
                    'required',
                    'string',
                    'size:2',
                    Rule::in(
                        array_keys(
                            config(
                                'esubiz_countries',
                                []
                            )
                        )
                    ),
                ],

                'primary_currency' => [
                    'required',
                    'string',
                    'size:3',
                    Rule::in(
                        array_keys(
                            config(
                                'esubiz_currencies',
                                []
                            )
                        )
                    ),
                ],

                'secondary_currencies' => [
                    'nullable',
                    'array',
                ],

                'secondary_currencies.*' => [
                    'string',
                    'size:3',
                    Rule::in(
                        array_keys(
                            config(
                                'esubiz_currencies',
                                []
                            )
                        )
                    ),
                    'distinct',
                ],

                'secondary_settings' => [
                    'nullable',
                    'array',
                ],

                'secondary_settings.*.markup_type' => [
                    'nullable',
                    Rule::in([
                        'percentage',
                        'fixed',
                    ]),
                ],

                'secondary_settings.*.markup_value' => [
                    'nullable',
                    'numeric',
                    'min:0',
                ],

                'language' => [
                    'required',
                    'string',
                    'size:2',
                    Rule::in(
                        array_keys(
                            config(
                                'esubiz_languages',
                                []
                            )
                        )
                    ),
                ],

                'date_format' => [
                    'required',
                    'string',
                    'max:40',
                ],

                'time_format' => [
                    'required',
                    'string',
                    'max:20',
                ],

                'week_start' => [
                    'required',
                    Rule::in([
                        'monday',
                        'sunday',
                        'saturday',
                    ]),
                ],

                'currency_position' => [
                    'required',
                    Rule::in([
                        'before',
                        'after',
                    ]),
                ],

                'number_format' => [
                    'required',
                    Rule::in([
                        '1,234.56',
                        '1.234,56',
                        '1 234.56',
                        '1 234,56',
                    ]),
                ],
            ]);

            $primaryCurrency = strtoupper(
                trim(
                    $validated['primary_currency']
                )
            );

            $secondaryCurrencies =
                array_values(
                    array_unique(
                        array_map(
                            fn ($currency) =>
                                strtoupper(
                                    trim(
                                        (string) $currency
                                    )
                                ),
                            $validated[
                                'secondary_currencies'
                            ] ?? []
                        )
                    )
                );

            /*
             * ESUBIZ_PRESERVE_SECONDARY_PRIMARY_V16
             *
             * Do NOT remove the current primary currency from the
             * saved Secondary Currency configuration.
             *
             * This preserves its secondary markup/settings so Admin
             * can later swap Primary and Secondary currencies without
             * losing the former configuration.
             *
             * Runtime pricing separately avoids rendering the current
             * primary as a redundant converted price.
             */

            $secondaryInput =
                $validated[
                    'secondary_settings'
                ] ?? [];

            $secondarySettings = [];

            foreach (
                $secondaryCurrencies
                as $currency
            ) {
                $input =
                    $secondaryInput[$currency]
                    ?? [];

                $secondarySettings[$currency] = [
                    'enabled' => true,

                    'markup_type' =>
                        $input['markup_type']
                        ?? 'percentage',

                    'markup_value' =>
                        max(
                            0,
                            (float) (
                                $input['markup_value']
                                ?? 0
                            )
                        ),
                ];
            }

            $settings->setMany([
                'platform.timezone' =>
                    $validated['timezone'],

                'platform.country' =>
                    strtoupper(
                        $validated['country']
                    ),

                'platform.currency.primary' =>
                    $primaryCurrency,

                'platform.language' =>
                    trim(
                        $validated['language']
                    ),

                'platform.date_format' =>
                    $validated['date_format'],

                'platform.time_format' =>
                    $validated['time_format'],

                'platform.week_start' =>
                    $validated['week_start'],

                'platform.currency_position' =>
                    $validated['currency_position'],

                'platform.number_format' =>
                    $validated['number_format'],
            ]);

            $settings->configureSecondaryCurrencies(
                $secondaryCurrencies,
                $secondarySettings
            );
        }


        if ($section === 'seo') {
            $validated = $request->validate([
                'seo_site_title' => [
                    'required',
                    'string',
                    'max:70',
                ],

                'seo_title_suffix' => [
                    'nullable',
                    'string',
                    'max:40',
                ],

                'seo_meta_description' => [
                    'required',
                    'string',
                    'max:180',
                ],

                'seo_meta_keywords' => [
                    'nullable',
                    'string',
                    'max:500',
                ],

                'seo_canonical_url' => [
                    'nullable',
                    'url',
                    'max:255',
                ],

                'seo_robots' => [
                    'required',
                    'string',
                    'max:255',
                ],

                'seo_og_title' => [
                    'nullable',
                    'string',
                    'max:100',
                ],

                'seo_og_description' => [
                    'nullable',
                    'string',
                    'max:200',
                ],

                'seo_twitter_card' => [
                    'required',
                    Rule::in([
                        'summary',
                        'summary_large_image',
                    ]),
                ],

                'seo_organization_name' => [
                    'required',
                    'string',
                    'max:100',
                ],

                'seo_organization_description' => [
                    'nullable',
                    'string',
                    'max:500',
                ],
            ]);

            $settings->setMany([
                'seo.site_title' =>
                    trim(
                        $validated['seo_site_title']
                    ),

                'seo.title_suffix' =>
                    trim(
                        (string) (
                            $validated[
                                'seo_title_suffix'
                            ] ?? ''
                        )
                    ),

                'seo.meta_description' =>
                    trim(
                        $validated[
                            'seo_meta_description'
                        ]
                    ),

                'seo.meta_keywords' =>
                    trim(
                        (string) (
                            $validated[
                                'seo_meta_keywords'
                            ] ?? ''
                        )
                    ),

                'seo.canonical_url' =>
                    rtrim(
                        trim(
                            (string) (
                                $validated[
                                    'seo_canonical_url'
                                ] ?? ''
                            )
                        ),
                        '/'
                    ),

                'seo.robots' =>
                    trim(
                        $validated['seo_robots']
                    ),

                'seo.og_title' =>
                    trim(
                        (string) (
                            $validated[
                                'seo_og_title'
                            ] ?? ''
                        )
                    ),

                'seo.og_description' =>
                    trim(
                        (string) (
                            $validated[
                                'seo_og_description'
                            ] ?? ''
                        )
                    ),

                'seo.twitter_card' =>
                    $validated[
                        'seo_twitter_card'
                    ],

                'seo.organization_name' =>
                    trim(
                        $validated[
                            'seo_organization_name'
                        ]
                    ),

                'seo.organization_description' =>
                    trim(
                        (string) (
                            $validated[
                                'seo_organization_description'
                            ] ?? ''
                        )
                    ),
            ]);
        }

        return redirect()
            ->route(
                'admin.site-settings.index',
                [
                    'tab' => 'main',
                    'sub' => $section,
                ]
            )
            ->with(
                'success',
                'Main site settings updated successfully.'
            );
    }
}
