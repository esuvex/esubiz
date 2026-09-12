{{-- ESUBIZ_CORE_INTERNAL_FOOTER_V47 --}}
@php
    /*
     * Canonical Core internal footer.
     *
     * The configured white/footer logo is authoritative for
     * authenticated Core pages.
     */
    $coreInternalFooterLogo =
        $settings['theme.corporate.footer_logo_path']
            ?? null;

    $coreInternalFooterLogoUrl =
        !empty($coreInternalFooterLogo)
            ? request()->getSchemeAndHttpHost()
                . '/media/'
                . implode(
                    '/',
                    array_map(
                        'rawurlencode',
                        explode(
                            '/',
                            ltrim(
                                $coreInternalFooterLogo,
                                '/'
                            )
                        )
                    )
                )
            : null;

    $coreInternalWebsiteName =
        $settings['website_name']
            ?? $website->name
            ?? 'Website';
@endphp

<footer
    class="core-internal-footer"
    style="
        margin-top:auto;
        background:#0b1739;
        border-top:1px solid rgba(255,255,255,.08);
        padding:20px 24px;
    "
>
    <div
        style="
            width:100%;
            display:flex;
            align-items:center;
            justify-content:space-between;
            gap:18px;
            flex-wrap:wrap;
        "
    >
        <div
            style="
                display:flex;
                align-items:center;
                min-height:34px;
            "
        >
            @if($coreInternalFooterLogoUrl)
                <img
                    src="{{ $coreInternalFooterLogoUrl }}"
                    alt="{{ $coreInternalWebsiteName }}"
                    style="
                        display:block;
                        max-width:150px;
                        max-height:42px;
                        width:auto;
                        height:auto;
                        object-fit:contain;
                    "
                >
            @else
                <strong
                    style="
                        color:#fff;
                        font-size:15px;
                        line-height:1.2;
                    "
                >
                    {{ $coreInternalWebsiteName }}
                </strong>
            @endif
        </div>

        <div
            style="
                color:#94a3b8;
                font-size:12px;
                line-height:1.5;
            "
        >
            &copy; {{ date('Y') }}
            {{ $coreInternalWebsiteName }}.
            All rights reserved.
        </div>
    </div>
</footer>
