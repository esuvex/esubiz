{{-- ESUBIZ_SHARED_REGISTRATION_AGREEMENTS_V21 --}}

@php
    /*
     * Expected structure:
     *
     * [
     *   [
     *     'id' => 'terms',
     *     'label' => 'Terms of Service',
     *     'url' => '/terms',
     *     'required' => true,
     *     'enabled' => true,
     *     'account_types' => ['user', 'developer'],
     *   ],
     * ]
     *
     * The data authority is intentionally external:
     *
     * - Central supplies Central agreements.
     * - Core supplies tenant/Core agreements.
     *
     * This partial only renders them.
     */

    $registrationAgreements =
        isset($registrationAgreements)
        && is_array($registrationAgreements)
            ? $registrationAgreements
            : [];

    $registrationAgreementAccountField =
        $registrationAgreementAccountField
        ?? 'account_role';

    $registrationAgreementDefaultAccountType =
        (string) (
            $registrationAgreementDefaultAccountType
            ?? 'user'
        );

    $registrationAgreementContext =
        (string) (
            $registrationAgreementContext
            ?? 'central'
        );

    $registrationAgreements =
        array_values(
            array_filter(
                $registrationAgreements,
                static function ($agreement) {
                    return is_array($agreement)
                        && (
                            !array_key_exists(
                                'enabled',
                                $agreement
                            )
                            || (bool) $agreement['enabled']
                        );
                }
            )
        );
@endphp

@if(!empty($registrationAgreements))
    <div
        class="esubiz-registration-agreements"
        data-esubiz-registration-agreements
        data-account-field="{{ $registrationAgreementAccountField }}"
        data-default-account="{{ $registrationAgreementDefaultAccountType }}"
        data-context="{{ $registrationAgreementContext }}"
    >
        @foreach($registrationAgreements as $agreement)
            @php
                $agreementId =
                    preg_replace(
                        '/[^a-zA-Z0-9_-]/',
                        '-',
                        (string) (
                            $agreement['id']
                            ?? 'agreement-' . $loop->iteration
                        )
                    );

                $agreementLabel =
                    trim(
                        (string) (
                            $agreement['label']
                            ?? 'Agreement'
                        )
                    );

                $agreementUrl =
                    trim(
                        (string) (
                            $agreement['url']
                            ?? '#'
                        )
                    );

                $agreementRequired =
                    (bool) (
                        $agreement['required']
                        ?? false
                    );

                $accountTypes =
                    $agreement['account_types']
                    ?? [];

                if (!is_array($accountTypes)) {
                    $accountTypes = [];
                }

                $accountTypes =
                    array_values(
                        array_filter(
                            array_map(
                                static fn ($value) =>
                                    strtolower(
                                        trim(
                                            (string) $value
                                        )
                                    ),
                                $accountTypes
                            )
                        )
                    );

                $accountTypesJson =
                    json_encode(
                        $accountTypes,
                        JSON_UNESCAPED_SLASHES
                    );
            @endphp

            <div
                class="esubiz-registration-agreement"
                data-esubiz-agreement
                data-account-types="{{ e($accountTypesJson) }}"
            >
                <label
                    class="esubiz-registration-agreement-label"
                    for="agreement-{{ $registrationAgreementContext }}-{{ $agreementId }}"
                >
                    <input
                        id="agreement-{{ $registrationAgreementContext }}-{{ $agreementId }}"
                        type="checkbox"
                        name="registration_agreements[]"
                        value="{{ $agreementId }}"
                        @if($agreementRequired)
                            required
                            data-esubiz-required-agreement="1"
                        @endif
                    >

                    <span>
                        I agree to the

                        <button
                            type="button"
                            class="esubiz-registration-agreement-link"
                            data-esubiz-agreement-preview
                            data-agreement-title="{{ $agreementLabel }}"
                            data-agreement-url="{{ $agreementUrl }}"
                        >
                            {{ $agreementLabel }}
                        </button>

                        @if($agreementRequired)
                            <span
                                class="esubiz-registration-required"
                                aria-label="Required"
                            >*</span>
                        @endif
                    </span>
                </label>
            </div>
        @endforeach
    </div>

    <div
        class="esubiz-agreement-modal"
        data-esubiz-agreement-modal
        hidden
        aria-hidden="true"
    >
        <div
            class="esubiz-agreement-modal-backdrop"
            data-esubiz-agreement-close
        ></div>

        <div
            class="esubiz-agreement-modal-dialog"
            role="dialog"
            aria-modal="true"
            aria-labelledby="esubiz-agreement-modal-title"
        >
            <div class="esubiz-agreement-modal-header">
                <h2
                    id="esubiz-agreement-modal-title"
                    data-esubiz-agreement-modal-title
                >
                    Agreement
                </h2>

                <button
                    type="button"
                    class="esubiz-agreement-modal-close"
                    data-esubiz-agreement-close
                    aria-label="Close"
                >
                    ×
                </button>
            </div>

            <div class="esubiz-agreement-modal-body">

                {{-- ESUBIZ_AGREEMENT_CONTENT_ONLY_MODAL_V22 --}}

                <div
                    class="esubiz-agreement-modal-loading"
                    data-esubiz-agreement-loading
                    hidden
                >
                    <div class="esubiz-agreement-loading-spinner"></div>

                    <p>
                        Loading agreement...
                    </p>
                </div>

                <div
                    class="esubiz-agreement-modal-content"
                    data-esubiz-agreement-content
                ></div>

                <div
                    class="esubiz-agreement-modal-fallback"
                    data-esubiz-agreement-fallback
                    hidden
                >
                    <p class="esubiz-agreement-fallback-title">
                        External agreement
                    </p>

                    <p>
                        This agreement is hosted outside this
                        website and cannot be displayed here
                        without also loading the external
                        website's navigation.
                    </p>

                    <a
                        href="#"
                        target="_blank"
                        rel="noopener noreferrer"
                        data-esubiz-agreement-open
                    >
                        Open agreement
                    </a>
                </div>
            </div>
        </div>
    </div>
    </div>

    @once
        <style>
            /* ESUBIZ_REGISTRATION_AGREEMENT_UI_V21 */

            .esubiz-registration-agreements {
                margin: 22px 0;
            }

            .esubiz-registration-agreement {
                margin: 0 0 12px;
            }

            .esubiz-registration-agreement[hidden] {
                display: none !important;
            }

            .esubiz-registration-agreement-label {
                display: flex;
                align-items: flex-start;
                gap: 10px;
                color: #667085;
                font-size: 13px;
                line-height: 1.55;
                cursor: pointer;
            }

            .esubiz-registration-agreement-label input {
                flex: 0 0 auto;
                width: 18px;
                height: 18px;
                margin: 2px 0 0;
                accent-color: #0b1f3a;
            }

            .esubiz-registration-agreement-link {
                display: inline;
                margin: 0;
                padding: 0;
                border: 0;
                background: transparent;
                color: #1769ff;
                font: inherit;
                font-weight: 700;
                text-decoration: none;
                cursor: pointer;
            }

            .esubiz-registration-agreement-link:hover {
                text-decoration: underline;
            }

            .esubiz-registration-required {
                color: #d92d20;
                font-weight: 800;
            }

            .esubiz-agreement-modal[hidden] {
                display: none !important;
            }

            .esubiz-agreement-modal {
                position: fixed;
                inset: 0;
                z-index: 99999;
                display: grid;
                place-items: center;
                padding: 22px;
            }

            .esubiz-agreement-modal-backdrop {
                position: absolute;
                inset: 0;
                background: rgba(7, 18, 36, .68);
                backdrop-filter: blur(3px);
            }

            .esubiz-agreement-modal-dialog {
                position: relative;
                z-index: 1;
                display: flex;
                flex-direction: column;
                width: min(100%, 900px);
                height: min(86vh, 760px);
                max-height: 86dvh;
                overflow: hidden;
                border-radius: 18px;
                background: #fff;
                box-shadow:
                    0 30px 90px rgba(7, 18, 36, .28);
            }

            .esubiz-agreement-modal-header {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 18px;
                min-height: 66px;
                padding: 14px 18px 14px 22px;
                border-bottom: 1px solid #e7ebf0;
            }

            .esubiz-agreement-modal-header h2 {
                margin: 0;
                color: #0b1f3a;
                font-size: 18px;
                line-height: 1.3;
                font-weight: 800;
            }

            .esubiz-agreement-modal-close {
                flex: 0 0 auto;
                display: grid;
                place-items: center;
                width: 40px;
                height: 40px;
                padding: 0;
                border: 1px solid #dde3eb;
                border-radius: 10px;
                background: #fff;
                color: #344054;
                font-size: 27px;
                line-height: 1;
                cursor: pointer;
            }

            .esubiz-agreement-modal-close:hover {
                background: #f6f8fb;
            }

            .esubiz-agreement-modal-body {
                position: relative;
                flex: 1 1 auto;
                min-height: 0;
                overflow: auto;
                background: #fff;
            }


            .esubiz-agreement-modal-content {
                width: 100%;
                max-width: 780px;
                margin: 0 auto;
                padding: 30px 30px 42px;
                color: #344054;
                font-size: 15px;
                line-height: 1.75;
                overflow-wrap: anywhere;
            }

            .esubiz-agreement-modal-content:empty {
                display: none;
            }

            .esubiz-agreement-modal-content h1,
            .esubiz-agreement-modal-content h2,
            .esubiz-agreement-modal-content h3,
            .esubiz-agreement-modal-content h4 {
                margin-top: 1.5em;
                margin-bottom: .65em;
                color: #0b1f3a;
                line-height: 1.3;
            }

            .esubiz-agreement-modal-content h1:first-child,
            .esubiz-agreement-modal-content h2:first-child,
            .esubiz-agreement-modal-content h3:first-child {
                margin-top: 0;
            }

            .esubiz-agreement-modal-content p {
                margin: 0 0 1em;
            }

            .esubiz-agreement-modal-content ul,
            .esubiz-agreement-modal-content ol {
                margin: 0 0 1em;
                padding-left: 1.5em;
            }

            .esubiz-agreement-modal-content img {
                max-width: 100%;
                height: auto;
            }

            .esubiz-agreement-modal-content table {
                width: 100%;
                border-collapse: collapse;
                margin: 18px 0;
            }

            .esubiz-agreement-modal-content th,
            .esubiz-agreement-modal-content td {
                padding: 10px;
                border: 1px solid #e4e7ec;
                vertical-align: top;
            }

            .esubiz-agreement-modal-loading {
                display: flex;
                min-height: 230px;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                gap: 14px;
                padding: 30px;
                color: #667085;
                text-align: center;
            }

            .esubiz-agreement-modal-loading[hidden] {
                display: none !important;
            }

            .esubiz-agreement-loading-spinner {
                width: 30px;
                height: 30px;
                border: 3px solid #e4e7ec;
                border-top-color: #0b1f3a;
                border-radius: 50%;
                animation:
                    esubizAgreementSpin .75s linear infinite;
            }

            @keyframes esubizAgreementSpin {
                to {
                    transform: rotate(360deg);
                }
            }


            .esubiz-agreement-modal-fallback {
                max-width: 560px;
                margin: 0 auto;
                padding: 48px 28px;
                color: #667085;
                text-align: center;
                line-height: 1.65;
            }

            .esubiz-agreement-fallback-title {
                margin: 0 0 8px;
                color: #0b1f3a;
                font-size: 18px;
                font-weight: 800;
            }

            .esubiz-agreement-modal-fallback a {
                color: #1769ff;
                font-weight: 700;
            }

            body.esubiz-agreement-modal-open {
                overflow: hidden;
            }

            @media (max-width: 640px) {
                .esubiz-agreement-modal {
                    padding: 10px;
                }

                .esubiz-agreement-modal-dialog {
                    width: 100%;
                    height: 91dvh;
                    max-height: 91dvh;
                    border-radius: 14px;
                }

                .esubiz-agreement-modal-header {
                    min-height: 58px;
                    padding: 10px 10px 10px 16px;
                }

                .esubiz-agreement-modal-header h2 {
                    font-size: 16px;
                }

                .esubiz-agreement-modal-content {
                    padding: 22px 18px 34px;
                    font-size: 14px;
                    line-height: 1.7;
                }
            }
        </style>

        <script>
            /*
             * ESUBIZ_REGISTRATION_AGREEMENT_JS_V22
             *
             * Same-origin page agreements are fetched and only
             * their meaningful content is displayed.
             *
             * Header, nav, footer, sidebars and scripts are
             * deliberately excluded from the modal.
             */
            document.addEventListener(
                'DOMContentLoaded',
                function () {
                    const roots =
                        document.querySelectorAll(
                            '[data-esubiz-registration-agreements]'
                        );

                    roots.forEach(function (root) {
                        const fieldName =
                            root.dataset.accountField || '';

                        const defaultAccount =
                            (
                                root.dataset.defaultAccount
                                || 'user'
                            ).toLowerCase();

                        const form =
                            root.closest('form');

                        const accountField =
                            form && fieldName
                                ? form.querySelector(
                                    '[name="'
                                    + CSS.escape(fieldName)
                                    + '"]'
                                )
                                : null;

                        function selectedAccountType() {
                            if (!accountField) {
                                return defaultAccount;
                            }

                            return (
                                accountField.value
                                || defaultAccount
                            )
                                .toString()
                                .trim()
                                .toLowerCase();
                        }

                        function updateAgreements() {
                            const selected =
                                selectedAccountType();

                            root.querySelectorAll(
                                '[data-esubiz-agreement]'
                            ).forEach(
                                function (item) {
                                    let types = [];

                                    try {
                                        types =
                                            JSON.parse(
                                                item.dataset.accountTypes
                                                || '[]'
                                            );
                                    } catch (error) {
                                        types = [];
                                    }

                                    const visible =
                                        !types.length
                                        || types.includes(selected)
                                        || types.includes('*')
                                        || types.includes('all');

                                    item.hidden = !visible;

                                    const checkbox =
                                        item.querySelector(
                                            'input[type="checkbox"]'
                                        );

                                    if (!checkbox) {
                                        return;
                                    }

                                    if (!visible) {
                                        checkbox.checked = false;
                                        checkbox.required = false;
                                        return;
                                    }

                                    checkbox.required =
                                        checkbox.dataset
                                            .esubizRequiredAgreement
                                        === '1';
                                }
                            );
                        }

                        if (accountField) {
                            accountField.addEventListener(
                                'change',
                                updateAgreements
                            );
                        }

                        updateAgreements();
                    });


                    const modal =
                        document.querySelector(
                            '[data-esubiz-agreement-modal]'
                        );

                    if (!modal) {
                        return;
                    }


                    const title =
                        modal.querySelector(
                            '[data-esubiz-agreement-modal-title]'
                        );

                    const content =
                        modal.querySelector(
                            '[data-esubiz-agreement-content]'
                        );

                    const loading =
                        modal.querySelector(
                            '[data-esubiz-agreement-loading]'
                        );

                    const fallback =
                        modal.querySelector(
                            '[data-esubiz-agreement-fallback]'
                        );

                    const openLink =
                        modal.querySelector(
                            '[data-esubiz-agreement-open]'
                        );

                    let activeTrigger = null;


                    function showLoading() {
                        if (content) {
                            content.innerHTML = '';
                        }

                        if (fallback) {
                            fallback.hidden = true;
                        }

                        if (loading) {
                            loading.hidden = false;
                        }
                    }


                    function hideLoading() {
                        if (loading) {
                            loading.hidden = true;
                        }
                    }


                    function showExternal(url) {
                        hideLoading();

                        if (content) {
                            content.innerHTML = '';
                        }

                        if (openLink) {
                            openLink.href = url;
                        }

                        if (fallback) {
                            fallback.hidden = false;
                        }
                    }


                    function isSameOrigin(url) {
                        try {
                            const parsed =
                                new URL(
                                    url,
                                    window.location.href
                                );

                            return (
                                parsed.origin
                                === window.location.origin
                            );
                        } catch (error) {
                            return false;
                        }
                    }


                    function cleanContent(root) {
                        if (!root) {
                            return null;
                        }

                        const clone =
                            root.cloneNode(true);

                        clone.querySelectorAll(
                            [
                                'header',
                                'nav',
                                'footer',
                                'aside',
                                'script',
                                'style',
                                'noscript',
                                'iframe',
                                '[role="navigation"]',
                                '.navbar',
                                '.nav',
                                '.header',
                                '.site-header',
                                '.footer',
                                '.site-footer',
                                '.sidebar',
                                '.breadcrumb',
                                '.breadcrumbs',
                                '.cookie-banner',
                                '.cookie-consent'
                            ].join(',')
                        ).forEach(
                            function (node) {
                                node.remove();
                            }
                        );

                        return clone;
                    }


                    function selectMainContent(documentNode) {
                        const selectors = [
                            '[data-esubiz-page-content]',
                            '[data-page-content]',
                            '.page-content',
                            '.site-page-content',
                            '.legal-content',
                            '.terms-content',
                            '.privacy-content',
                            'main article',
                            'article',
                            'main',
                            '.content'
                        ];

                        for (
                            const selector
                            of selectors
                        ) {
                            const found =
                                documentNode.querySelector(
                                    selector
                                );

                            if (
                                found
                                && found.textContent.trim()
                            ) {
                                return found;
                            }
                        }

                        return documentNode.body;
                    }


                    async function loadInternalAgreement(
                        url
                    ) {
                        showLoading();

                        try {
                            const response =
                                await fetch(
                                    url,
                                    {
                                        method: 'GET',
                                        credentials: 'same-origin',
                                        headers: {
                                            'X-Requested-With':
                                                'XMLHttpRequest',
                                            'Accept':
                                                'text/html'
                                        }
                                    }
                                );

                            if (!response.ok) {
                                throw new Error(
                                    'Agreement returned HTTP '
                                    + response.status
                                );
                            }

                            const html =
                                await response.text();

                            const parsed =
                                new DOMParser()
                                    .parseFromString(
                                        html,
                                        'text/html'
                                    );

                            const selected =
                                selectMainContent(
                                    parsed
                                );

                            const cleaned =
                                cleanContent(
                                    selected
                                );

                            hideLoading();

                            if (
                                !cleaned
                                || !cleaned.textContent.trim()
                            ) {
                                throw new Error(
                                    'No readable page content found.'
                                );
                            }

                            if (content) {
                                content.innerHTML =
                                    cleaned.innerHTML;

                                /*
                                 * Any relative links inside the
                                 * extracted document remain usable.
                                 */
                                content
                                    .querySelectorAll(
                                        'a[href]'
                                    )
                                    .forEach(
                                        function (link) {
                                            try {
                                                link.href =
                                                    new URL(
                                                        link.getAttribute(
                                                            'href'
                                                        ),
                                                        url
                                                    ).href;
                                            } catch (error) {
                                                // Leave original.
                                            }
                                        }
                                    );

                                content
                                    .querySelectorAll(
                                        'img[src]'
                                    )
                                    .forEach(
                                        function (image) {
                                            try {
                                                image.src =
                                                    new URL(
                                                        image.getAttribute(
                                                            'src'
                                                        ),
                                                        url
                                                    ).href;
                                            } catch (error) {
                                                // Leave original.
                                            }
                                        }
                                    );
                            }
                        } catch (error) {
                            /*
                             * Same-origin page exists but cannot
                             * be cleanly extracted. Do not fall
                             * back to a full-page iframe.
                             */
                            hideLoading();

                            if (content) {
                                content.innerHTML =
                                    '<div style="text-align:center;'
                                    + 'padding:44px 20px;'
                                    + 'color:#667085;">'
                                    + '<strong style="display:block;'
                                    + 'margin-bottom:8px;'
                                    + 'color:#0b1f3a;">'
                                    + 'Agreement preview unavailable'
                                    + '</strong>'
                                    + 'Use the link below to open '
                                    + 'the agreement page.'
                                    + '</div>';
                            }

                            if (openLink) {
                                openLink.href = url;
                            }

                            if (fallback) {
                                fallback.hidden = false;
                            }
                        }
                    }


                    function openModal(trigger) {
                        activeTrigger = trigger;

                        const url =
                            trigger.dataset.agreementUrl
                            || '#';

                        const label =
                            trigger.dataset.agreementTitle
                            || 'Agreement';

                        if (title) {
                            title.textContent = label;
                        }

                        if (openLink) {
                            openLink.href = url;
                        }

                        modal.hidden = false;

                        modal.setAttribute(
                            'aria-hidden',
                            'false'
                        );

                        document.body.classList.add(
                            'esubiz-agreement-modal-open'
                        );

                        if (isSameOrigin(url)) {
                            loadInternalAgreement(
                                url
                            );
                        } else {
                            showExternal(
                                url
                            );
                        }

                        const closeButton =
                            modal.querySelector(
                                '.esubiz-agreement-modal-close'
                            );

                        if (closeButton) {
                            closeButton.focus();
                        }
                    }


                    function closeModal() {
                        modal.hidden = true;

                        modal.setAttribute(
                            'aria-hidden',
                            'true'
                        );

                        document.body.classList.remove(
                            'esubiz-agreement-modal-open'
                        );

                        if (content) {
                            content.innerHTML = '';
                        }

                        if (fallback) {
                            fallback.hidden = true;
                        }

                        if (loading) {
                            loading.hidden = true;
                        }

                        if (activeTrigger) {
                            activeTrigger.focus();
                        }
                    }


                    document.addEventListener(
                        'click',
                        function (event) {
                            const trigger =
                                event.target.closest(
                                    '[data-esubiz-agreement-preview]'
                                );

                            if (trigger) {
                                event.preventDefault();

                                openModal(
                                    trigger
                                );

                                return;
                            }

                            if (
                                event.target.closest(
                                    '[data-esubiz-agreement-close]'
                                )
                            ) {
                                closeModal();
                            }
                        }
                    );


                    document.addEventListener(
                        'keydown',
                        function (event) {
                            if (
                                event.key === 'Escape'
                                && !modal.hidden
                            ) {
                                closeModal();
                            }
                        }
                    );
                }
            );
        </script>
    @endonce
@endif
