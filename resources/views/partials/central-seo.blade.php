{{--
    ESUBIZ_CENTRAL_GLOBAL_SEO_V13

    Global Central Esubiz SEO source of truth.

    Public pages:
    - inherit Central SEO settings
    - receive path-correct canonical URLs
    - receive Open Graph / Twitter metadata
    - receive Organization + Website structured data

    Internal pages are protected separately with noindex.
--}}
@php
    $esSeoV13 = app(
        \App\Services\Platform\CentralSiteSettingsService::class
    )->all();

    $esSeoTitleV13 =
        trim(
            (string) (
                $esSeoV13[
                    'seo.site_title'
                ] ?? ''
            )
        )
        ?: 'Esubiz — Build, Manage & Grow Your Business Online';

    $esSeoDescriptionV13 =
        trim(
            (string) (
                $esSeoV13[
                    'seo.meta_description'
                ] ?? ''
            )
        );

    $esSeoKeywordsV13 =
        trim(
            (string) (
                $esSeoV13[
                    'seo.meta_keywords'
                ] ?? ''
            )
        );

    /*
     * seo.canonical_url is the authoritative
     * public Central base URL.
     *
     * Each public page receives its own canonical
     * by appending the current request path.
     */
    $esSeoBaseUrlV13 =
        rtrim(
            trim(
                (string) (
                    $esSeoV13[
                        'seo.canonical_url'
                    ] ?? ''
                )
            )
            ?: (string) config(
                'app.url',
                'https://esubiz.com'
            ),
            '/'
        );

    $esSeoCurrentPathV13 =
        trim(
            request()->path(),
            '/'
        );

    $esSeoCanonicalV13 =
        $esSeoCurrentPathV13 === ''
        ? $esSeoBaseUrlV13
        : $esSeoBaseUrlV13
            . '/'
            . $esSeoCurrentPathV13;

    $esSeoRobotsV13 =
        trim(
            (string) (
                $esSeoV13[
                    'seo.robots'
                ] ?? ''
            )
        )
        ?: 'index,follow,max-image-preview:large,max-snippet:-1,max-video-preview:-1';

    $esSeoOgTitleV13 =
        trim(
            (string) (
                $esSeoV13[
                    'seo.og_title'
                ] ?? ''
            )
        )
        ?: $esSeoTitleV13;

    $esSeoOgDescriptionV13 =
        trim(
            (string) (
                $esSeoV13[
                    'seo.og_description'
                ] ?? ''
            )
        )
        ?: $esSeoDescriptionV13;

    $esSeoTwitterCardV13 =
        trim(
            (string) (
                $esSeoV13[
                    'seo.twitter_card'
                ] ?? ''
            )
        )
        ?: 'summary_large_image';

    $esSeoOrganizationV13 =
        trim(
            (string) (
                $esSeoV13[
                    'seo.organization_name'
                ] ?? ''
            )
        )
        ?: 'Esubiz';

    $esSeoOrganizationDescriptionV13 =
        trim(
            (string) (
                $esSeoV13[
                    'seo.organization_description'
                ] ?? ''
            )
        );

    /*
     * Same canonical uploaded logo.
     * No duplicate social-image file.
     */
    $esSeoLogoV13 =
        url(
            '/media/branding/esubiz-logo.png'
        );

    $esOrganizationSchemaV13 = [
        '@context' => 'https://schema.org',
        '@type' => 'Organization',
        'name' => $esSeoOrganizationV13,
        'url' => $esSeoBaseUrlV13,
        'logo' => $esSeoLogoV13,
        'description' =>
            $esSeoOrganizationDescriptionV13,
    ];

    $esWebsiteSchemaV13 = [
        '@context' => 'https://schema.org',
        '@type' => 'WebSite',
        'name' => $esSeoOrganizationV13,
        'url' => $esSeoBaseUrlV13,
        'description' =>
            $esSeoDescriptionV13,
    ];

    $esJsonFlagsV13 =
        JSON_UNESCAPED_SLASHES
        | JSON_UNESCAPED_UNICODE
        | JSON_HEX_TAG
        | JSON_HEX_AMP
        | JSON_HEX_APOS
        | JSON_HEX_QUOT;
@endphp

<meta
    name="description"
    content="{{ $esSeoDescriptionV13 }}"
>

@if($esSeoKeywordsV13 !== '')
<meta
    name="keywords"
    content="{{ $esSeoKeywordsV13 }}"
>
@endif

<meta
    name="robots"
    content="{{ $esSeoRobotsV13 }}"
>

<link
    rel="canonical"
    href="{{ $esSeoCanonicalV13 }}"
>

<meta
    property="og:type"
    content="website"
>

<meta
    property="og:site_name"
    content="{{ $esSeoOrganizationV13 }}"
>

<meta
    property="og:title"
    content="{{ $esSeoOgTitleV13 }}"
>

<meta
    property="og:description"
    content="{{ $esSeoOgDescriptionV13 }}"
>

<meta
    property="og:url"
    content="{{ $esSeoCanonicalV13 }}"
>

<meta
    property="og:image"
    content="{{ $esSeoLogoV13 }}"
>

<meta
    name="twitter:card"
    content="{{ $esSeoTwitterCardV13 }}"
>

<meta
    name="twitter:title"
    content="{{ $esSeoOgTitleV13 }}"
>

<meta
    name="twitter:description"
    content="{{ $esSeoOgDescriptionV13 }}"
>

<meta
    name="twitter:image"
    content="{{ $esSeoLogoV13 }}"
>

<script type="application/ld+json">{!! json_encode(
    $esOrganizationSchemaV13,
    $esJsonFlagsV13
) !!}</script>

<script type="application/ld+json">{!! json_encode(
    $esWebsiteSchemaV13,
    $esJsonFlagsV13
) !!}</script>
