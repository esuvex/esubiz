{{-- ESUBIZ_CORE_SHARED_FAVICON_V6 --}}
@php
    /*
     * ESUBIZ_CORE_UNIVERSAL_FAVICON_AUTHORITY_V8
     *
     * Universal Core favicon contract:
     *
     * 1. A context-specific explicit override wins when supplied.
     * 2. site_favicon_path is the canonical website favicon.
     * 3. Historical theme favicon remains compatibility fallback only.
     *
     * The canonical value is resolved directly from the active tenant
     * database so public, Core admin and authentication layouts do not
     * depend on each caller remembering to inject Site Settings.
     */
    $coreCanonicalFavicon = null;
    $coreLegacyThemeFavicon = null;

    try {
        $coreFaviconDb =
            \Illuminate\Support\Facades\DB::connection(
                'website_tenant'
            );

        $coreFaviconRows =
            $coreFaviconDb
                ->table('site_settings')
                ->whereIn(
                    'key',
                    [
                        'site_favicon_path',
                        'theme.corporate.favicon_path',
                    ]
                )
                ->pluck('value', 'key');

        $coreCanonicalFavicon =
            trim(
                (string) (
                    $coreFaviconRows[
                        'site_favicon_path'
                    ]
                    ?? ''
                )
            );

        $coreLegacyThemeFavicon =
            trim(
                (string) (
                    $coreFaviconRows[
                        'theme.corporate.favicon_path'
                    ]
                    ?? ''
                )
            );

    } catch (\Throwable $coreFaviconException) {
        /*
         * Off-server / non-tenant compatibility:
         * allow the existing resolver below to continue.
         */
    }

    /*
     * Existing callers may explicitly provide a favicon override.
     * Empty values are NOT overrides.
     */
    $coreExplicitFaviconOverride = null;

    foreach (
        [
            $authFavicon ?? null,
            $faviconOverride ?? null,
        ]
        as $coreFaviconOverrideCandidate
    ) {
        $coreFaviconOverrideCandidate =
            trim(
                (string) $coreFaviconOverrideCandidate
            );

        if ($coreFaviconOverrideCandidate !== '') {
            $coreExplicitFaviconOverride =
                $coreFaviconOverrideCandidate;
            break;
        }
    }

    $coreEffectiveFavicon =
        $coreExplicitFaviconOverride
        ?: (
            $coreCanonicalFavicon
            ?: (
                $coreLegacyThemeFavicon
                ?: null
            )
        );

    /*
     * Feed the effective value into the existing shared resolver.
     * This keeps its media URL/version/browser-sync implementation
     * authoritative instead of creating a second renderer.
     */
    if (!empty($coreEffectiveFavicon)) {
        $faviconOverride = $coreEffectiveFavicon;

        if (!isset($settings) || !is_array($settings)) {
            $settings = [];
        }

        $settings['site_favicon_path'] =
            $coreEffectiveFavicon;
    }
@endphp

@php
    /*
     * Plug-and-play favicon resolver for Core.
     *
     * Ownership:
     *      this Core website only.
     *
     * Canonical source of truth:
     *      site_favicon_path
     *
     * Compatibility fallbacks:
     *      website_favicon_path
     *      favicon_path
     *      theme.corporate.favicon_path
     *
     * Applies equally to:
     *      - SaaS Core
     *      - off-server Core
     *
     * It never inherits the Central Esubiz favicon.
     *
     * The resolver deliberately accepts the different settings
     * bags that existing Core screens may already expose so that
     * pages such as Site Settings -> Auth do not lose the favicon
     * merely because their local variable shape differs.
     *
     * Future internal pages only need to use the canonical Core
     * admin layout.
     *
     * Future public pages/themes only need to use a canonical Core
     * public head containing this partial.
     */

    $coreFaviconSources = [];

    if (
        isset($settings)
        && is_array($settings)
    ) {
        $coreFaviconSources[] = $settings;
    }

    if (
        isset($coreSettings)
        && is_array($coreSettings)
    ) {
        $coreFaviconSources[] = $coreSettings;
    }

    if (
        isset($siteConfig)
        && is_array($siteConfig)
    ) {
        $coreFaviconSources[] = $siteConfig;
    }

    /*
     * Some Core pages expose settings from the Website model
     * rather than through the conventional $settings variable.
     */
    if (
        isset($website)
        && is_object($website)
        && method_exists($website, 'getAttribute')
    ) {
        try {
            $websiteSettings =
                $website->getAttribute('settings');

            if (
                is_array($websiteSettings)
            ) {
                $coreFaviconSources[] =
                    $websiteSettings;
            } elseif (
                is_string($websiteSettings)
                && trim($websiteSettings) !== ''
            ) {
                $decodedWebsiteSettings =
                    json_decode(
                        $websiteSettings,
                        true
                    );

                if (
                    is_array(
                        $decodedWebsiteSettings
                    )
                ) {
                    $coreFaviconSources[] =
                        $decodedWebsiteSettings;
                }
            }
        } catch (\Throwable $e) {
            /*
             * The favicon must never break a Core page.
             */
        }
    }

    /*
     * ESUBIZ_CORE_FAVICON_PORTABLE_SETTINGS_FALLBACK_V4
     *
     * Canonical Core favicon hydration.
     *
     * Controllers such as Core Site Settings already provide
     * a normalized $siteConfig['favicon_path'] value.
     *
     * Other Core pages may intentionally provide only their
     * own page data. The shared favicon resolver therefore
     * reproduces the same existing Core normalization when a
     * favicon has not already been supplied.
     *
     * Existing Core Site Settings contract:
     *
     *     site_favicon_path
     *         -> theme.corporate.favicon_path
     *         -> normalized favicon_path
     *
     * SaaS uses the active tenant database.
     * Off-server Core uses the same local Core schema.
     *
     * No image optimization or other media processing occurs
     * during page rendering.
     */
    $coreFaviconAvailable = false;

    foreach (
        $coreFaviconSources
        as $coreFaviconSource
    ) {
        if (!is_array($coreFaviconSource)) {
            continue;
        }

        $candidate =
            $coreFaviconSource[
                'site_favicon_path'
            ]
            ?? $coreFaviconSource[
                'website_favicon_path'
            ]
            ?? $coreFaviconSource[
                'favicon_path'
            ]
            ?? data_get(
                $coreFaviconSource,
                'theme.corporate.favicon_path'
            )
            ?? null;

        if (
            is_string($candidate)
            && trim($candidate) !== ''
        ) {
            $coreFaviconAvailable = true;
            break;
        }
    }

    if (!$coreFaviconAvailable) {
        try {
            /*
             * Use the exact full site_settings loading pattern
             * already used by Core Site Settings.
             */
            $coreBrandingSettings =
                \Illuminate\Support\Facades\DB::connection(
                    'tenant'
                )
                    ->table('site_settings')
                    ->pluck('value', 'key')
                    ->all();

            $coreCanonicalFaviconPath =
                $coreBrandingSettings[
                    'site_favicon_path'
                ]
                ?? $coreBrandingSettings[
                    'theme.corporate.favicon_path'
                ]
                ?? null;

            if (
                is_string(
                    $coreCanonicalFaviconPath
                )
                && trim(
                    $coreCanonicalFaviconPath
                ) !== ''
            ) {
                /*
                 * Normalize into the same shape that working
                 * Core Site Settings supplies to this partial.
                 */
                $coreFaviconSources[] = [
                    'favicon_path' =>
                        $coreCanonicalFaviconPath,
                ];
            }
        } catch (\Throwable $e) {
            /*
             * Missing/incomplete tenant context must never
             * cause Core rendering to fail.
             */
        }
    }

    $coreFaviconPath = null;

    foreach (
        $coreFaviconSources
        as $coreFaviconSource
    ) {
        $candidate =
            $coreFaviconSource[
                'site_favicon_path'
            ]
            ?? $coreFaviconSource[
                'website_favicon_path'
            ]
            ?? $coreFaviconSource[
                'favicon_path'
            ]
            ?? data_get(
                $coreFaviconSource,
                'theme.corporate.favicon_path'
            )
            ?? null;

        if (
            is_string($candidate)
            && trim($candidate) !== ''
        ) {
            $coreFaviconPath =
                trim($candidate);

            break;
        }
    }

    /*
     * Public themes may expose the compatibility favicon directly
     * through their theme configuration.
     */
    if (
        !$coreFaviconPath
        && isset($theme)
        && is_array($theme)
    ) {
        $candidate =
            $theme['favicon_path']
            ?? data_get(
                $theme,
                'corporate.favicon_path'
            )
            ?? null;

        if (
            is_string($candidate)
            && trim($candidate) !== ''
        ) {
            $coreFaviconPath =
                trim($candidate);
        }
    }

    $coreFaviconUrl = null;
    $coreFaviconType = null;

    if ($coreFaviconPath) {
        if (
            str_starts_with(
                $coreFaviconPath,
                'http://'
            )
            || str_starts_with(
                $coreFaviconPath,
                'https://'
            )
        ) {
            /*
             * Compatibility with an existing absolute favicon.
             * New Core uploads remain owned by Core storage.
             */
            $coreFaviconUrl =
                $coreFaviconPath;
        } else {
            $coreFaviconMediaPath =
                ltrim(
                    $coreFaviconPath,
                    '/'
                );

            /*
             * Existing Core media contract:
             *
             *     /media/{stored Core media path}
             *
             * Path segments are encoded independently so directory
             * ownership remains unchanged.
             */
            $coreFaviconUrl =
                request()
                    ->getSchemeAndHttpHost()
                . '/media/'
                . implode(
                    '/',
                    array_map(
                        'rawurlencode',
                        explode(
                            '/',
                            $coreFaviconMediaPath
                        )
                    )
                );
        }

        $coreFaviconExtension =
            strtolower(
                pathinfo(
                    parse_url(
                        $coreFaviconPath,
                        PHP_URL_PATH
                    ) ?: $coreFaviconPath,
                    PATHINFO_EXTENSION
                )
            );

        $coreFaviconType =
            match ($coreFaviconExtension) {
                'svg' =>
                    'image/svg+xml',

                'ico' =>
                    'image/x-icon',

                'webp' =>
                    'image/webp',

                'jpg',
                'jpeg' =>
                    'image/jpeg',

                'png' =>
                    'image/png',

                default =>
                    null,
            };
    }


    /*
     * ESUBIZ_CORE_FAVICON_BROWSER_CACHE_V5
     *
     * Core favicons are resolved once from the website-owned
     * stored media path. Browsers can cache favicon state very
     * aggressively, including an earlier missing favicon.
     *
     * Add a stable version derived from the stored favicon path.
     *
     * A replacement upload normally receives a different stored
     * path, therefore its version also changes automatically.
     *
     * This performs no image/media processing at page-render time.
     */
    if (
        $coreFaviconUrl
        && $coreFaviconPath
    ) {
        $coreFaviconVersion =
            substr(
                hash(
                    'sha256',
                    $coreFaviconPath
                ),
                0,
                12
            );

        $coreFaviconUrl .=
            (
                str_contains(
                    $coreFaviconUrl,
                    '?'
                )
                    ? '&'
                    : '?'
            )
            . 'v='
            . rawurlencode(
                $coreFaviconVersion
            );
    }

@endphp

@if($coreFaviconUrl)
    <link
        rel="icon"
        href="{{ $coreFaviconUrl }}"
        @if($coreFaviconType)
            type="{{ $coreFaviconType }}"
        @endif
    >

    <link
        rel="shortcut icon"
        href="{{ $coreFaviconUrl }}"
        @if($coreFaviconType)
            type="{{ $coreFaviconType }}"
        @endif
    >

    <link
        rel="apple-touch-icon"
        href="{{ $coreFaviconUrl }}"
    >

    {{--
        ESUBIZ_CORE_FAVICON_BROWSER_SYNC_V6

        Chrome and other browsers can retain a missing/old favicon
        state even after the canonical <link rel="icon"> changes.

        Keep the normal server-rendered favicon links above as the
        source of truth, then synchronize the browser favicon DOM
        with that exact same versioned Core favicon URL.

        No media processing occurs here.
    --}}
    <script>
        (() => {
            const faviconUrl = @json($coreFaviconUrl);
            const faviconType = @json($coreFaviconType);

            if (!faviconUrl) {
                return;
            }

            const syncCoreFavicon = () => {
                const head = document.head;

                if (!head) {
                    return;
                }

                /*
                 * Remove favicon declarations that may have been
                 * retained/injected by another page state.
                 */
                head.querySelectorAll(
                    'link[rel="icon"],' +
                    'link[rel="shortcut icon"],' +
                    'link[rel="apple-touch-icon"]'
                ).forEach((node) => {
                    node.remove();
                });

                const icon = document.createElement('link');
                icon.rel = 'icon';
                icon.href = faviconUrl;

                if (faviconType) {
                    icon.type = faviconType;
                }

                head.appendChild(icon);

                const shortcut = document.createElement('link');
                shortcut.rel = 'shortcut icon';
                shortcut.href = faviconUrl;

                if (faviconType) {
                    shortcut.type = faviconType;
                }

                head.appendChild(shortcut);

                const apple = document.createElement('link');
                apple.rel = 'apple-touch-icon';
                apple.href = faviconUrl;

                head.appendChild(apple);
            };

            /*
             * Run immediately for normal navigation.
             */
            syncCoreFavicon();

            /*
             * Run again when the page is restored from browser
             * back/forward cache.
             */
            window.addEventListener(
                'pageshow',
                syncCoreFavicon,
                { once: true }
            );
        })();
    </script>
@endif
