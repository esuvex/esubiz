{{-- ESUBIZ_CENTRAL_GLOBAL_FAVICON_PARTIAL_V2 --}}
@php
    /*
     * Central owns its own favicon independently from Core websites.
     *
     * Source of truth:
     *
     * Central Site Settings
     *      -> CentralSiteSettingsService::faviconPath()
     *      -> CentralMediaService::url()
     *      -> every canonical Central <head>
     *
     * The stable branding alias remains only as a safe fallback.
     */

    $centralFaviconPath = null;
    $centralFaviconUrl = url('/media/branding/favicon.png');
    $centralFaviconType = 'image/png';

    try {
        $centralFaviconPath =
            app(
                \App\Services\Platform\CentralSiteSettingsService::class
            )->faviconPath();

        if (
            is_string($centralFaviconPath)
            && trim($centralFaviconPath) !== ''
        ) {
            $centralFaviconPath =
                trim($centralFaviconPath);

            $centralFaviconUrl =
                app(
                    \App\Services\Media\CentralMediaService::class
                )->url(
                    $centralFaviconPath
                );

            $extension =
                strtolower(
                    pathinfo(
                        parse_url(
                            $centralFaviconPath,
                            PHP_URL_PATH
                        ) ?: $centralFaviconPath,
                        PATHINFO_EXTENSION
                    )
                );

            $centralFaviconType =
                match ($extension) {
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
    } catch (\Throwable $e) {
        /*
         * A favicon problem must never break a Central page.
         * Keep the stable branding alias as the fallback.
         */
        $centralFaviconPath = null;
        $centralFaviconUrl =
            url('/media/branding/favicon.png');

        $centralFaviconType =
            'image/png';
    }
@endphp

<link
    rel="icon"
    href="{{ $centralFaviconUrl }}"
    @if($centralFaviconType)
        type="{{ $centralFaviconType }}"
    @endif
>

<link
    rel="shortcut icon"
    href="{{ $centralFaviconUrl }}"
    @if($centralFaviconType)
        type="{{ $centralFaviconType }}"
    @endif
>

<link
    rel="apple-touch-icon"
    href="{{ $centralFaviconUrl }}"
>
