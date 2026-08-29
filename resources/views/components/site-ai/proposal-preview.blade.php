{{--
    ================================================================
    ESUBIZ UNIVERSAL AI PROPOSAL PREVIEW V1
    ================================================================

    Shared preview / approval interface for:

    - Theme sections
    - Theme generation
    - Logo
    - Favicon
    - Images / media
    - Page Builder
    - Products
    - Hotel content
    - Forms
    - Documents
    - Future AI capabilities

    AI proposes.
    Esubiz validates.
    User approves.
    Registered capability applies to the real destination.

    IMPORTANT:
    This component does not persist website content directly.
--}}

<div
    id="esubiz-ai-proposal-overlay"
    class="esubiz-ai-proposal-overlay"
    hidden
    aria-hidden="true"
>
    <section
        id="esubiz-ai-proposal-panel"
        class="esubiz-ai-proposal-panel"
        role="dialog"
        aria-modal="true"
        aria-labelledby="esubiz-ai-proposal-title"
    >
        {{-- Header --}}
        <header class="esubiz-ai-proposal-header">
            <div class="esubiz-ai-proposal-heading">
                <div class="esubiz-ai-proposal-eyebrow">
                    AI Preview
                </div>

                <h2
                    id="esubiz-ai-proposal-title"
                    class="esubiz-ai-proposal-title"
                >
                    Review AI Proposal
                </h2>

                <p
                    id="esubiz-ai-proposal-summary"
                    class="esubiz-ai-proposal-summary"
                    hidden
                ></p>
            </div>

            <button
                type="button"
                id="esubiz-ai-proposal-close"
                class="esubiz-ai-proposal-close"
                aria-label="Close AI proposal preview"
            >
                &times;
            </button>
        </header>


        {{-- Scrollable content area --}}
        <div
            id="esubiz-ai-proposal-scroll"
            class="esubiz-ai-proposal-scroll"
        >
            <div
                id="esubiz-ai-proposal-status"
                class="esubiz-ai-proposal-status"
                hidden
            ></div>

            <div
                id="esubiz-ai-proposal-preview"
                class="esubiz-ai-proposal-preview"
            >
                <div class="esubiz-ai-proposal-empty">
                    No proposal loaded.
                </div>
            </div>
        </div>


        {{-- Sticky action area --}}
        <footer class="esubiz-ai-proposal-footer">
            <div
                id="esubiz-ai-proposal-progress"
                class="esubiz-ai-proposal-progress"
                aria-live="polite"
            ></div>

            <div class="esubiz-ai-proposal-actions">
                <button
                    type="button"
                    id="esubiz-ai-proposal-reject"
                    class="
                        esubiz-ai-proposal-button
                        esubiz-ai-proposal-button-secondary
                    "
                >
                    Discard
                </button>

                <button
                    type="button"
                    id="esubiz-ai-proposal-approve"
                    class="
                        esubiz-ai-proposal-button
                        esubiz-ai-proposal-button-primary
                    "
                >
                    Approve & Apply
                </button>
            </div>
        </footer>
    </section>
</div>


<style>
    /*
     * ============================================================
     * ESUBIZ UNIVERSAL AI PROPOSAL PREVIEW V1
     * RESPONSIVE + SCROLLABLE ON ALL DEVICES
     * ============================================================
     */

    .esubiz-ai-proposal-overlay {
        position: fixed;
        inset: 0;
        z-index: 2147483000;

        display: flex;
        align-items: center;
        justify-content: center;

        width: 100%;
        height: 100vh;
        height: 100dvh;

        padding:
            max(16px, env(safe-area-inset-top))
            max(16px, env(safe-area-inset-right))
            max(16px, env(safe-area-inset-bottom))
            max(16px, env(safe-area-inset-left));

        box-sizing: border-box;

        background:
            rgba(15, 23, 42, 0.62);

        backdrop-filter:
            blur(5px);

        -webkit-backdrop-filter:
            blur(5px);

        overflow: hidden;
    }

    .esubiz-ai-proposal-overlay[hidden] {
        display: none !important;
    }


    .esubiz-ai-proposal-panel {
        width: min(1120px, 100%);

        /*
         * Never allow the modal itself to exceed the viewport.
         * The body below becomes the scroll container.
         */
        max-height:
            calc(
                100dvh
                - 32px
                - env(safe-area-inset-top)
                - env(safe-area-inset-bottom)
            );

        display: flex;
        flex-direction: column;

        overflow: hidden;

        background: #ffffff;

        border-radius: 20px;

        box-shadow:
            0 24px 80px
            rgba(15, 23, 42, 0.28);
    }


    .esubiz-ai-proposal-header {
        flex: 0 0 auto;

        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 20px;

        padding: 22px 24px;

        border-bottom:
            1px solid #e5e7eb;

        background: #ffffff;
    }


    .esubiz-ai-proposal-heading {
        min-width: 0;
    }


    .esubiz-ai-proposal-eyebrow {
        margin-bottom: 5px;

        font-size: 12px;
        line-height: 1.4;
        font-weight: 700;

        letter-spacing: 0.08em;
        text-transform: uppercase;

        color: #64748b;
    }


    .esubiz-ai-proposal-title {
        margin: 0;

        font-size: 21px;
        line-height: 1.3;
        font-weight: 700;

        color: #0f172a;

        overflow-wrap: anywhere;
    }


    .esubiz-ai-proposal-summary {
        margin:
            8px 0 0;

        max-width: 760px;

        font-size: 14px;
        line-height: 1.6;

        color: #64748b;

        overflow-wrap: anywhere;
    }


    .esubiz-ai-proposal-close {
        flex: 0 0 auto;

        width: 40px;
        height: 40px;

        display: inline-flex;
        align-items: center;
        justify-content: center;

        padding: 0;

        border: 0;
        border-radius: 10px;

        background: #f8fafc;

        color: #475569;

        font-size: 28px;
        line-height: 1;

        cursor: pointer;
    }


    /*
     * This is the primary scrolling surface.
     *
     * min-height: 0 is critical inside a flex column;
     * without it, large previews can force the panel beyond
     * the viewport on some browsers.
     */
    .esubiz-ai-proposal-scroll {
        flex: 1 1 auto;

        min-width: 0;
        min-height: 0;

        overflow-x: hidden;
        overflow-y: auto;

        overscroll-behavior: contain;

        -webkit-overflow-scrolling: touch;

        padding: 24px;

        background: #f8fafc;
    }


    .esubiz-ai-proposal-preview {
        width: 100%;
        min-width: 0;
    }


    .esubiz-ai-proposal-status {
        margin-bottom: 18px;

        padding: 12px 14px;

        border:
            1px solid #e2e8f0;

        border-radius: 10px;

        background: #ffffff;

        font-size: 13px;
        line-height: 1.5;

        color: #475569;

        overflow-wrap: anywhere;
    }


    .esubiz-ai-proposal-empty {
        padding: 48px 20px;

        text-align: center;

        color: #64748b;
    }


    .esubiz-ai-proposal-item {
        min-width: 0;

        margin-bottom: 16px;

        padding: 18px;

        border:
            1px solid #e2e8f0;

        border-radius: 14px;

        background: #ffffff;
    }


    .esubiz-ai-proposal-item:last-child {
        margin-bottom: 0;
    }


    .esubiz-ai-proposal-item-label {
        margin-bottom: 9px;

        font-size: 12px;
        line-height: 1.4;
        font-weight: 700;

        letter-spacing: 0.04em;
        text-transform: uppercase;

        color: #64748b;

        overflow-wrap: anywhere;
    }


    .esubiz-ai-proposal-item-value {
        min-width: 0;

        font-size: 15px;
        line-height: 1.65;

        color: #0f172a;

        white-space: pre-wrap;
        overflow-wrap: anywhere;
        word-break: break-word;
    }


    .esubiz-ai-proposal-item-value img,
    .esubiz-ai-proposal-item-value video,
    .esubiz-ai-proposal-item-value iframe {
        display: block;

        max-width: 100%;
        height: auto;

        margin-top: 10px;

        border-radius: 12px;
    }


    .esubiz-ai-proposal-item-value pre {
        max-width: 100%;

        margin: 0;

        padding: 14px;

        overflow: auto;

        border-radius: 10px;

        background: #f1f5f9;

        white-space: pre-wrap;
        overflow-wrap: anywhere;
    }


    /*
     * Footer remains visible while the preview body scrolls.
     */
    .esubiz-ai-proposal-footer {
        flex: 0 0 auto;

        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;

        padding:
            16px 24px
            max(
                16px,
                env(safe-area-inset-bottom)
            );

        border-top:
            1px solid #e5e7eb;

        background: #ffffff;
    }


    .esubiz-ai-proposal-progress {
        min-width: 0;

        font-size: 13px;
        line-height: 1.45;

        color: #64748b;

        overflow-wrap: anywhere;
    }


    .esubiz-ai-proposal-actions {
        flex: 0 0 auto;

        display: flex;
        align-items: center;
        gap: 10px;
    }


    .esubiz-ai-proposal-button {
        min-height: 42px;

        padding: 10px 17px;

        border-radius: 10px;

        font-size: 14px;
        line-height: 1.4;
        font-weight: 600;

        cursor: pointer;
    }


    .esubiz-ai-proposal-button:disabled {
        opacity: 0.55;
        cursor: not-allowed;
    }


    .esubiz-ai-proposal-button-secondary {
        border:
            1px solid #cbd5e1;

        background: #ffffff;

        color: #334155;
    }


    .esubiz-ai-proposal-button-primary {
        border:
            1px solid #0f172a;

        background: #0f172a;

        color: #ffffff;
    }


    /*
     * Tablet / smaller screens.
     */
    @media (max-width: 768px) {
        .esubiz-ai-proposal-overlay {
            align-items: stretch;

            padding:
                max(
                    8px,
                    env(safe-area-inset-top)
                )
                max(
                    8px,
                    env(safe-area-inset-right)
                )
                max(
                    8px,
                    env(safe-area-inset-bottom)
                )
                max(
                    8px,
                    env(safe-area-inset-left)
                );
        }


        .esubiz-ai-proposal-panel {
            width: 100%;

            max-height:
                calc(
                    100dvh
                    - 16px
                    - env(safe-area-inset-top)
                    - env(safe-area-inset-bottom)
                );

            border-radius: 16px;
        }


        .esubiz-ai-proposal-header {
            padding: 17px;
        }


        .esubiz-ai-proposal-scroll {
            padding: 16px;
        }


        .esubiz-ai-proposal-footer {
            align-items: stretch;
            flex-direction: column;

            padding:
                13px 16px
                max(
                    13px,
                    env(safe-area-inset-bottom)
                );
        }


        .esubiz-ai-proposal-actions {
            width: 100%;
        }


        .esubiz-ai-proposal-button {
            flex: 1 1 0;
        }
    }


    /*
     * Phones.
     *
     * Use effectively full-screen preview so generated content
     * has maximum usable space while retaining independent
     * vertical scrolling.
     */
    @media (max-width: 480px) {
        .esubiz-ai-proposal-overlay {
            padding: 0;
        }


        .esubiz-ai-proposal-panel {
            width: 100%;
            height: 100dvh;
            max-height: 100dvh;

            border-radius: 0;
        }


        .esubiz-ai-proposal-header {
            padding:
                max(
                    14px,
                    env(safe-area-inset-top)
                )
                14px
                14px;
        }


        .esubiz-ai-proposal-title {
            font-size: 18px;
        }


        .esubiz-ai-proposal-scroll {
            padding: 13px;
        }


        .esubiz-ai-proposal-item {
            padding: 14px;
        }


        .esubiz-ai-proposal-footer {
            padding:
                12px
                12px
                max(
                    12px,
                    env(safe-area-inset-bottom)
                );
        }
    }
</style>


<script>
(function () {
    'use strict';

    if (
        window.EsubizAiProposalPreview
        && window.EsubizAiProposalPreview.__v1
    ) {
        return;
    }


    const overlay =
        document.getElementById(
            'esubiz-ai-proposal-overlay'
        );

    if (!overlay) {
        return;
    }


    const panel =
        document.getElementById(
            'esubiz-ai-proposal-panel'
        );

    const scroll =
        document.getElementById(
            'esubiz-ai-proposal-scroll'
        );

    const title =
        document.getElementById(
            'esubiz-ai-proposal-title'
        );

    const summary =
        document.getElementById(
            'esubiz-ai-proposal-summary'
        );

    const preview =
        document.getElementById(
            'esubiz-ai-proposal-preview'
        );

    const status =
        document.getElementById(
            'esubiz-ai-proposal-status'
        );

    const progress =
        document.getElementById(
            'esubiz-ai-proposal-progress'
        );

    const closeButton =
        document.getElementById(
            'esubiz-ai-proposal-close'
        );

    const approveButton =
        document.getElementById(
            'esubiz-ai-proposal-approve'
        );

    const rejectButton =
        document.getElementById(
            'esubiz-ai-proposal-reject'
        );

    /*
     * ESUBIZ_AI_FOOTER_CLOSE_BUTTON_V2
     *
     * Dedicated footer Close button.
     * It occupies the same primary-action position as
     * Approve & Apply after successful application.
     */
    const footerCloseButton =
        approveButton.cloneNode(
            true
        );

    footerCloseButton.id =
        'esubiz-ai-proposal-footer-close';

    footerCloseButton.type =
        'button';

    footerCloseButton.textContent =
        'Close';

    footerCloseButton.hidden =
        true;

    footerCloseButton.disabled =
        false;

    footerCloseButton.removeAttribute(
        'data-applied'
    );

    approveButton.insertAdjacentElement(
        'afterend',
        footerCloseButton
    );

    footerCloseButton.addEventListener(
        'click',
        (event) => {
            event.preventDefault();
            event.stopPropagation();

            window.location.reload();
        }
    );



    let currentProposal = null;
    let previousOverflow = '';
    let busy = false;

    /*
     * ESUBIZ_AI_PROPOSAL_SUCCESS_CLOSE_RELOAD_V1
     *
     * After an approved proposal has been written successfully,
     * closing the preview reloads the normal tenant editor.
     * This refreshes saved theme values and clears the temporary
     * Site AI conversation/UI state.
     */
    let reloadOnClose = false;

    closeButton.addEventListener(
        'click',
        () => {
            if (!reloadOnClose) {
                return;
            }

            window.location.reload();
        }
    );


    function escapeHtml(value) {
        return String(
            value ?? ''
        )
            .replaceAll(
                '&',
                '&amp;'
            )
            .replaceAll(
                '<',
                '&lt;'
            )
            .replaceAll(
                '>',
                '&gt;'
            )
            .replaceAll(
                '"',
                '&quot;'
            )
            .replaceAll(
                "'",
                '&#039;'
            );
    }


    function unwrapValue(value) {
        if (
            value
            && typeof value === 'object'
            && !Array.isArray(value)
            && Object.keys(value).length === 1
            && Object.prototype.hasOwnProperty.call(
                value,
                'value'
            )
        ) {
            return value.value;
        }

        return value;
    }


    function readableTarget(target) {
        return String(
            target || 'Generated Content'
        )
            .replaceAll(
                '.',
                ' '
            )
            .replaceAll(
                '_',
                ' '
            )
            .replaceAll(
                '-',
                ' '
            )
            .replace(
                /\b\w/g,
                character =>
                    character.toUpperCase()
            );
    }


    function renderValue(value) {
        value =
            unwrapValue(
                value
            );

        if (
            value === null
            || typeof value === 'undefined'
        ) {
            return '<em>No value</em>';
        }

        if (
            typeof value === 'object'
        ) {
            return (
                '<pre>'
                + escapeHtml(
                    JSON.stringify(
                        value,
                        null,
                        2
                    )
                )
                + '</pre>'
            );
        }

        return escapeHtml(
            value
        );
    }
    /*
     * ESUBIZ_AI_PROPOSAL_IMAGE_PREVIEW_V2
     *
     * Generated images must be visible before approval.
     * This renderer accepts object, JSON-string, data URL,
     * raw base64 and nested provider image representations.
     */
    function normalizeProposalImageValue(
        value,
        mime = 'image/jpeg',
        depth = 0
    ) {
        if (
            value === null
            || value === undefined
            || depth > 6
        ) {
            return '';
        }

        if (typeof value === 'string') {
            const trimmed =
                value.trim();

            if (!trimmed) {
                return '';
            }

            if (
                trimmed.startsWith('data:image/')
                || trimmed.startsWith('https://')
                || trimmed.startsWith('http://')
                || trimmed.startsWith('/')
            ) {
                return trimmed;
            }

            if (
                (
                    trimmed.startsWith('{')
                    && trimmed.endsWith('}')
                )
                || (
                    trimmed.startsWith('[')
                    && trimmed.endsWith(']')
                )
            ) {
                try {
                    return normalizeProposalImageValue(
                        JSON.parse(trimmed),
                        mime,
                        depth + 1
                    );
                } catch (error) {
                    // Continue to base64 detection.
                }
            }

            const compact =
                trimmed.replace(/\s+/g, '');

            if (
                compact.length > 1000
                && /^[A-Za-z0-9+/]+={0,2}$/.test(
                    compact
                )
            ) {
                return (
                    'data:'
                    + String(
                        mime || 'image/jpeg'
                    )
                    + ';base64,'
                    + compact
                );
            }

            return '';
        }

        if (Array.isArray(value)) {
            for (const entry of value) {
                const found =
                    normalizeProposalImageValue(
                        entry,
                        mime,
                        depth + 1
                    );

                if (found) {
                    return found;
                }
            }

            return '';
        }

        if (typeof value === 'object') {
            const detectedMime =
                value.mime_type
                ?? value.mime
                ?? mime
                ?? 'image/jpeg';

            const preferredKeys = [
                'data_url',
                'preview_url',
                'url',
                'result',
                'data',
                'base64',
                'asset_reference',
                'proposed_value',
                'value',
                'reference'
            ];

            for (const key of preferredKeys) {
                if (
                    !Object.prototype.hasOwnProperty.call(
                        value,
                        key
                    )
                ) {
                    continue;
                }

                const found =
                    normalizeProposalImageValue(
                        value[key],
                        detectedMime,
                        depth + 1
                    );

                if (found) {
                    return found;
                }
            }

            for (
                const nested of Object.values(value)
            ) {
                const found =
                    normalizeProposalImageValue(
                        nested,
                        detectedMime,
                        depth + 1
                    );

                if (found) {
                    return found;
                }
            }
        }

        return '';
    }


    function renderGeneratedProposalImages(
        proposal
    ) {
        const container =
            document.getElementById(
                'esubiz-ai-proposal-preview'
            );

        if (!container) {
            return;
        }

        const items =
            Array.isArray(proposal?.items)
                ? proposal.items
                : [];

        const cards =
            container.querySelectorAll(
                '.esubiz-ai-proposal-item'
            );

        items.forEach(
            (item, index) => {
                const target =
                    String(
                        item?.target_key
                        ?? item?.target
                        ?? ''
                    ).toLowerCase();

                const type =
                    String(
                        item?.item_type
                        ?? item?.type
                        ?? ''
                    ).toLowerCase();

                const looksLikeImage =
                    type === 'image'
                    || target.endsWith(
                        '_image_path'
                    )
                    || target.includes(
                        'image'
                    )
                    || target.includes(
                        'photo'
                    );

                if (!looksLikeImage) {
                    return;
                }

                const source =
                    normalizeProposalImageValue(
                        item,
                        item?.asset_reference
                            ?.mime_type
                            ?? 'image/jpeg'
                    );

                if (!source) {
                    return;
                }

                const card =
                    cards[index];

                if (!card) {
                    return;
                }

                const value =
                    card.querySelector(
                        '.esubiz-ai-proposal-item-value'
                    );

                if (!value) {
                    return;
                }

                value.innerHTML =
                    '';

                const image =
                    document.createElement(
                        'img'
                    );

                image.src =
                    source;

                image.alt =
                    String(
                        item?.label
                        ?? item?.target_key
                        ?? item?.target
                        ?? 'AI generated image'
                    );

                image.loading =
                    'eager';

                image.decoding =
                    'async';

                image.style.display =
                    'block';

                image.style.width =
                    '100%';

                image.style.height =
                    'auto';

                image.style.maxHeight =
                    '420px';

                image.style.objectFit =
                    'cover';

                image.style.borderRadius =
                    '12px';

                value.appendChild(
                    image
                );
            }
        );
    }




    function renderProposal(proposal) {

        /*
         * Every newly opened proposal starts with the ordinary
         * pending action state.
         */
        footerCloseButton.hidden =
            true;

        footerCloseButton.disabled =
            false;

        approveButton.textContent =
            'Approve & Apply';

        approveButton.removeAttribute(
            'data-applied'
        );

        currentProposal =
            proposal || null;

        title.textContent =
            proposal?.title
            || 'Review AI Proposal';

        if (proposal?.summary) {
            summary.textContent =
                proposal.summary;

            summary.hidden =
                false;
        } else {
            summary.textContent =
                '';

            summary.hidden =
                true;
        }


        if (proposal?.status) {
            status.textContent =
                'Status: '
                + String(
                    proposal.status
                );

            status.hidden =
                false;
        } else {
            status.hidden =
                true;
        }


        const items =
            Array.isArray(
                proposal?.items
            )
                ? proposal.items
                : [];


        if (!items.length) {
            preview.innerHTML =
                '<div class="esubiz-ai-proposal-empty">'
                + 'No generated changes to preview.'
                + '</div>';
        } else {
            preview.innerHTML =
                items
                    .map(
                        item => {
                            return (
                                '<article class="esubiz-ai-proposal-item">'
                                + '<div class="esubiz-ai-proposal-item-label">'
                                + escapeHtml(
                                    readableTarget(
                                        item.target_key
                                    )
                                )
                                + '</div>'
                                + '<div class="esubiz-ai-proposal-item-value">'
                                + renderValue(
                                    item.proposed_value
                                )
                                + '</div>'
                                + '</article>'
                            );
                        }
                    )
                    .join('');
        }
        renderGeneratedProposalImages(
            proposal
        );




        const pending =
            proposal?.status === 'pending';


        approveButton.disabled =
            !pending;

        rejectButton.disabled =
            !pending;


        approveButton.hidden =
            !pending;

        rejectButton.hidden =
            !pending;


        progress.textContent =
            pending
                ? 'Review the generated changes before applying them.'
                : (
                    proposal?.status
                        ? 'Proposal '
                            + proposal.status
                            + '.'
                        : ''
                );


        /*
         * Every new proposal begins at the top,
         * including on mobile.
         */
        if (scroll) {
            scroll.scrollTop = 0;
        }
    }


    function open(proposal) {
        renderProposal(
            proposal
        );

        previousOverflow =
            document.documentElement
                .style
                .overflow;

        document.documentElement
            .style
            .overflow =
                'hidden';

        overlay.hidden =
            false;

        overlay.setAttribute(
            'aria-hidden',
            'false'
        );

        requestAnimationFrame(
            () => {
                closeButton?.focus();
            }
        );
    }


    function close() {
        if (busy) {
            return;
        }

        overlay.hidden =
            true;

        overlay.setAttribute(
            'aria-hidden',
            'true'
        );

        document.documentElement
            .style
            .overflow =
                previousOverflow;
    }


    function setBusy(
        state,
        message = ''
    ) {
        busy =
            Boolean(state);

        approveButton.disabled =
            busy;

        rejectButton.disabled =
            busy;

        closeButton.disabled =
            busy;

        progress.textContent =
            message;
    }


    /*
     * Expose one generic interface.
     *
     * The Site AI assistant will call open() when it receives:
     *
     * mode: "proposal"
     */
    window.EsubizAiProposalPreview = {
        __v1: true,

        open,

        close,

        render:
            renderProposal,

        current:
            () => currentProposal,

        setBusy,
    };


    closeButton.addEventListener(
        'click',
        close
    );


    overlay.addEventListener(
        'click',
        event => {
            if (
                event.target === overlay
                && !busy
            ) {
                close();
            }
        }
    );


    document.addEventListener(
        'keydown',
        event => {
            if (
                event.key === 'Escape'
                && !overlay.hidden
                && !busy
            ) {
                close();
            }
        }
    );


    /*
     * ============================================================
     * ESUBIZ_AI_PROPOSAL_ACTIONS_V1
     * ============================================================
     *
     * The preview UI NEVER writes website content itself.
     *
     * Approve:
     *   Controller
     *   -> Approval Coordinator
     *   -> Capability Applier
     *   -> Website Destination
     *   -> Tenant / off-server normal storage.
     *
     * Reject:
     *   Proposal is discarded without touching website content.
     */

    function csrfToken() {
        return document
            .querySelector(
                'meta[name="csrf-token"]'
            )
            ?.getAttribute(
                'content'
            )
            || '';
    }


    function proposalBaseUrl() {
        if (
            !currentProposal
            || !currentProposal.uuid
        ) {
            throw new Error(
                'No AI proposal is currently loaded.'
            );
        }

        return (
            '/admin/ai/proposals/'
            + encodeURIComponent(
                currentProposal.uuid
            )
        );
    }


    async function proposalRequest(
        action
    ) {
        const url =
            proposalBaseUrl()
            + '/'
            + action;

        const response =
            await fetch(
                url,
                {
                    method:
                        'POST',

                    credentials:
                        'same-origin',

                    headers: {
                        'Accept':
                            'application/json',

                        'Content-Type':
                            'application/json',

                        'X-CSRF-TOKEN':
                            csrfToken(),

                        'X-Requested-With':
                            'XMLHttpRequest',
                    },

                    body:
                        JSON.stringify({})
                }
            );

        let data = null;

        try {
            data =
                await response.json();
        } catch (error) {
            data = null;
        }

        if (!response.ok) {
            throw new Error(
                data?.message
                || 'The AI proposal request failed.'
            );
        }

        if (!data?.proposal) {
            throw new Error(
                'The AI proposal response is incomplete.'
            );
        }

        return data;
    }


    approveButton.addEventListener(
        'click',
        async () => {

        /*
         * ESUBIZ_AI_APPROVE_TO_CLOSE_BUTTON_V1
         *
         * Once application succeeds, the primary footer action
         * becomes Close in the exact former Approve position.
         */
        if (
            approveButton.dataset.applied
            === '1'
        ) {
            window.location.reload();
            return;
        }

            if (
                busy
                || !currentProposal
            ) {
                return;
            }

            setBusy(
                true,
                'Applying approved changes...'
            );

            try {
                const data =
                    await proposalRequest(
                        'approve'
                    );

                renderProposal(
                    data.proposal
                );

                progress.textContent =
                    data.message
                    || 'Approved changes have been applied.';

                /*
                 * Successful proposals now enter a completed state.
                 *
                 * The proposal has already been persisted by the
                 * capability applier, so there are no more proposal
                 * actions to perform. The admin only needs to close
                 * the preview and return to the refreshed editor.
                 */
                reloadOnClose = true;

                /*
                 * Successful apply:
                 *
                 * Approve & Apply disappears and a real,
                 * independent Close button takes its exact place.
                 */
                approveButton.hidden =
                    true;

                approveButton.disabled =
                    true;

                rejectButton.hidden =
                    true;

                rejectButton.disabled =
                    true;

                footerCloseButton.hidden =
                    false;

                footerCloseButton.disabled =
                    false;
                /*
                 * ESUBIZ_AI_APPROVED_ACTION_STATE_V3
                 *
                 * The proposal has been applied successfully.
                 * Approve and Discard must remain hidden.
                 * The dedicated footer Close button becomes the
                 * only primary action.
                 */
                approveButton.hidden =
                    true;

                approveButton.disabled =
                    true;

                rejectButton.hidden =
                    true;

                rejectButton.disabled =
                    true;

                footerCloseButton.hidden =
                    false;

                footerCloseButton.disabled =
                    false;

                closeButton.hidden =
                    false;

                closeButton.disabled =
                    false;

                closeButton.setAttribute(
                    'aria-label',
                    'Close and refresh'
                );


                /*
                 * Other Esubiz interfaces can listen for this
                 * event and refresh their normal editor/content.
                 *
                 * Example:
                 * Theme Customizer may reload its saved About
                 * values after approval.
                 */
                window.dispatchEvent(
                    new CustomEvent(
                        'esubiz:ai-proposal-applied',
                        {
                            detail: {
                                proposal:
                                    data.proposal
                            }
                        }
                    )
                );

            } catch (error) {
                progress.textContent =
                    error?.message
                    || 'Unable to apply this proposal.';

            } finally {
                busy = false;

                closeButton.disabled =
                    false;

                const pending =
                    currentProposal?.status
                    === 'pending';

                approveButton.disabled =
                    !pending;

                rejectButton.disabled =
                    rejectButton.dataset.discardComplete === 'true'
                        ? false
                        : !pending;
            }
        }
    );


    rejectButton.addEventListener(
        'click',
        async () => {
            /*
             * ESUBIZ_AI_REGENERATE_ANOTHER_CLICK_V3
             *
             * Once discard has completed, this same button becomes
             * the return-to-chat action instead of rejecting again.
             */
            if (
                rejectButton.dataset.discardComplete === 'true'
            ) {
                /*
                 * ESUBIZ_AI_REGENERATE_ANOTHER_CLOSE_FIX_V4
                 *
                 * Return directly to the existing AI conversation
                 * after a proposal has been discarded.
                 */
                close();
                return;
            }

            if (
                busy
                || !currentProposal
            ) {
                return;
            }

            setBusy(
                true,
                'Discarding proposal...'
            );

            try {
                const data =
                    await proposalRequest(
                        'reject'
                    );

                renderProposal(
                    data.proposal
                );

                progress.textContent =
                    data.message
                    || 'Proposal discarded.';

                /*
                 * ESUBIZ_AI_DISCARDED_REGENERATE_ANOTHER_V1
                 *
                 * A discarded proposal stays visible long enough for
                 * the user to confirm the result. The former Discard
                 * action then becomes Regenerate Another.
                 *
                 * Regenerate Another does not submit a new AI request.
                 * It simply closes this preview and returns the user
                 * to the existing AI conversation so they can continue.
                 */
                approveButton.hidden =
                    true;

                approveButton.disabled =
                    true;

                rejectButton.hidden =
                    false;

                rejectButton.disabled =
                    false;

                /*
                 * ESUBIZ_AI_REGENERATE_ANOTHER_ACTIVE_BUTTON_V3
                 */
                rejectButton.classList.remove(
                    'esubiz-ai-proposal-button-secondary'
                );

                rejectButton.classList.add(
                    'esubiz-ai-proposal-button-primary'
                );

                rejectButton.textContent =
                    'Regenerate Another';

                rejectButton.dataset.discardComplete =
                    'true';

                window.dispatchEvent(
                    new CustomEvent(
                        'esubiz:ai-proposal-rejected',
                        {
                            detail: {
                                proposal:
                                    data.proposal
                            }
                        }
                    )
                );

            } catch (error) {
                progress.textContent =
                    error?.message
                    || 'Unable to discard this proposal.';

            } finally {
                busy = false;

                closeButton.disabled =
                    false;

                const pending =
                    currentProposal?.status
                    === 'pending';

                approveButton.disabled =
                    !pending;

                rejectButton.disabled =
                    rejectButton.dataset.discardComplete === 'true'
                        ? false
                        : !pending;
            }
        }
    );
})();
</script>
