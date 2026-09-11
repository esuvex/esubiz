{{-- ESUBIZ_CENTRAL_DASHBOARD_NOTICE_RENDERER_V24 --}}

@php
    $esCentralNotices =
        collect($centralDashboardNotices ?? []);

    $esCentralUserId =
        (int) (auth()->id() ?? 0);

    $esCentralNoticeImageUrl = function ($notice) {
        if (empty($notice->image_path)) {
            return !empty($notice->image_url)
                ? $notice->image_url
                : null;
        }

        try {
            $media = app(
                \App\Services\Media\CentralMediaService::class
            );

            foreach ([
                'url',
                'resolveUrl',
                'getUrl',
            ] as $method) {
                if (method_exists($media, $method)) {
                    return $media->{$method}(
                        $notice->image_path
                    );
                }
            }
        } catch (\Throwable $e) {
            //
        }

        try {
            return \Illuminate\Support\Facades\Storage::disk(
                'public'
            )->url(
                $notice->image_path
            );
        } catch (\Throwable $e) {
            return null;
        }
    };

    $esStaticNotices =
        $esCentralNotices
            ->filter(
                fn ($notice) =>
                    !$notice->rotation_enabled
            )
            ->values();

    $esRotatingNotices =
        $esCentralNotices
            ->filter(
                fn ($notice) =>
                    (bool) $notice->rotation_enabled
            )
            ->values();
@endphp

@if($esCentralNotices->isNotEmpty())
    <section
        id="esCentralDashboardNoticesV24"
        class="es-central-notices-v24"
        data-user-id="{{ $esCentralUserId }}"
    >
        <style>
            .es-central-notices-v24 {
                width:100%;
                margin:0 0 22px;
            }

            .es-central-notice-v24 {
                position:relative;
                overflow:hidden;
                width:100%;
                margin:0 0 14px;
                border:1px solid #e7eaf0;
                border-radius:16px;
                background:#fff;
                box-shadow:0 5px 18px rgba(11,31,58,.06);
            }

            .es-central-notice-v24[data-variant="info"] {
                border-left:4px solid #2563eb;
            }

            .es-central-notice-v24[data-variant="success"] {
                border-left:4px solid #16a34a;
            }

            .es-central-notice-v24[data-variant="warning"] {
                border-left:4px solid #d97706;
            }

            .es-central-notice-v24[data-variant="danger"] {
                border-left:4px solid #dc2626;
            }

            .es-central-notice-v24[data-variant="primary"] {
                border-left:4px solid #0b1f3a;
            }

            .es-central-notice-inner-v24 {
                padding:18px;
            }

            .es-central-notice-title-v24 {
                margin:0 42px 8px 0;
                color:#0b1f3a;
                font-size:16px;
                line-height:1.35;
                font-weight:700;
            }

            .es-central-notice-message-v24 {
                color:#455268;
                font-size:14px;
                line-height:1.65;
            }

            .es-central-notice-message-v24 > :first-child {
                margin-top:0;
            }

            .es-central-notice-message-v24 > :last-child {
                margin-bottom:0;
            }

            .es-central-notice-message-v24 a {
                color:#0b1f3a;
                font-weight:600;
                text-decoration:underline;
            }

            .es-central-notice-dismiss-v24 {
                position:absolute;
                top:12px;
                right:12px;
                display:flex;
                align-items:center;
                justify-content:center;
                width:30px;
                height:30px;
                padding:0;
                border:0;
                border-radius:50%;
                background:#f3f5f8;
                color:#657084;
                font-size:18px;
                line-height:1;
                cursor:pointer;
            }

            .es-central-notice-dismiss-v24:hover {
                background:#e8ecf2;
                color:#0b1f3a;
            }

            .es-central-notice-image-button-v24 {
                display:block;
                width:100%;
                padding:0;
                border:0;
                background:none;
                cursor:zoom-in;
            }

            .es-central-notice-image-v24 {
                display:block;
                width:100%;
                aspect-ratio:2 / 1;
                max-height:360px;
                object-fit:cover;
            }

            .es-central-rotating-slot-v24:empty {
                display:none;
            }

            .es-central-image-modal-v24 {
                position:fixed;
                inset:0;
                z-index:999999;
                display:none;
                align-items:center;
                justify-content:center;
                padding:24px;
                background:rgba(5,12,24,.82);
            }

            .es-central-image-modal-v24.is-open {
                display:flex;
            }

            .es-central-image-dialog-v24 {
                position:relative;
                width:min(1100px, 96vw);
                max-height:90vh;
                overflow:auto;
                border-radius:14px;
                background:#fff;
                box-shadow:0 20px 60px rgba(0,0,0,.28);
            }

            .es-central-image-dialog-v24 img {
                display:block;
                width:100%;
                height:auto;
                object-fit:contain;
            }

            .es-central-image-close-v24 {
                position:sticky;
                top:10px;
                z-index:2;
                float:right;
                margin:10px 10px -42px 0;
                width:34px;
                height:34px;
                border:0;
                border-radius:50%;
                background:rgba(11,31,58,.9);
                color:#fff;
                font-size:20px;
                cursor:pointer;
            }

            @media (max-width: 767px) {
                .es-central-notice-v24 {
                    border-radius:13px;
                }

                .es-central-notice-inner-v24 {
                    padding:15px;
                }

                .es-central-notice-title-v24 {
                    font-size:15px;
                }

                .es-central-notice-image-v24 {
                    max-height:260px;
                }

                .es-central-image-modal-v24 {
                    padding:10px;
                }

                .es-central-image-dialog-v24 {
                    width:100%;
                    max-height:94vh;
                }
            }
        </style>

        {{-- Non-rotating notices always stack --}}
        <div id="esCentralStaticNoticesV24">
            @foreach($esStaticNotices as $notice)
                @php
                    $imageUrl =
                        $esCentralNoticeImageUrl(
                            $notice
                        );
                @endphp

                <article
                    class="es-central-notice-v24"
                    data-central-notice-id="{{ $notice->id }}"
                    data-variant="{{ $notice->variant ?: 'info' }}"
                >
                    @if($imageUrl)
                        <button
                            type="button"
                            class="es-central-notice-image-button-v24"
                            data-es-image-preview="{{ $imageUrl }}"
                            aria-label="Preview notice image"
                        >
                            <img
                                src="{{ $imageUrl }}"
                                alt=""
                                class="es-central-notice-image-v24"
                            >
                        </button>
                    @endif

                    <div class="es-central-notice-inner-v24">
                        @if($notice->dismissible)
                            <button
                                type="button"
                                class="es-central-notice-dismiss-v24"
                                data-es-dismiss-central-notice="{{ $notice->id }}"
                                aria-label="Dismiss notice"
                            >
                                &times;
                            </button>
                        @endif

                        @if(!empty($notice->title))
                            <h3 class="es-central-notice-title-v24">
                                {{ $notice->title }}
                            </h3>
                        @endif

                        <div class="es-central-notice-message-v24">
                            {!! $notice->message !!}
                        </div>
                    </div>
                </article>
            @endforeach
        </div>

        {{-- Rotation happens only while this dashboard is visible --}}
        @if($esRotatingNotices->isNotEmpty())
            <div
                id="esCentralRotatingSlotV24"
                class="es-central-rotating-slot-v24"
            ></div>

            {{-- ESUBIZ_CENTRAL_NOTICE_ROTATION_JSON_FIX_V35 --}}
            @php
                $esCentralRotatingPayloadV35 = $esRotatingNotices
                    ->map(function ($notice) use ($esCentralNoticeImageUrl) {
                        return [
                            'id' => (int) $notice->id,
                            'title' => (string) ($notice->title ?? ''),
                            'message_html' => (string) ($notice->message ?? ''),
                            'variant' => (string) ($notice->variant ?: 'info'),
                            'image_url' => $esCentralNoticeImageUrl($notice),
                            'dismissible' => (bool) $notice->dismissible,
                            'rotation_seconds' => max(
                                3,
                                min(
                                    120,
                                    (int) ($notice->rotation_seconds ?: 8)
                                )
                            ),
                        ];
                    })
                    ->values()
                    ->all();
            @endphp

            <script type="application/json" id="esCentralRotatingDataV24">{!! json_encode(
                $esCentralRotatingPayloadV35,
                JSON_HEX_TAG
                | JSON_HEX_APOS
                | JSON_HEX_AMP
                | JSON_HEX_QUOT
                | JSON_UNESCAPED_SLASHES
                | JSON_UNESCAPED_UNICODE
            ) !!}</script>
        @endif

        <div
            id="esCentralImageModalV24"
            class="es-central-image-modal-v24"
            aria-hidden="true"
        >
            <div class="es-central-image-dialog-v24">
                <button
                    type="button"
                    class="es-central-image-close-v24"
                    aria-label="Close image preview"
                >
                    &times;
                </button>

                <img
                    id="esCentralImagePreviewV24"
                    src=""
                    alt=""
                >
            </div>
        </div>

        <script>
            (function () {
                const root =
                    document.getElementById(
                        'esCentralDashboardNoticesV24'
                    );

                if (!root) {
                    return;
                }

                const userId =
                    root.dataset.userId || '0';

                const dismissedKey =
                    function (noticeId) {
                        return (
                            'esubiz_central_dashboard_notice_dismissed_v24_'
                            + userId
                            + '_'
                            + noticeId
                        );
                    };

                const isDismissed =
                    function (noticeId) {
                        return (
                            localStorage.getItem(
                                dismissedKey(noticeId)
                            ) === '1'
                        );
                    };

                const dismiss =
                    function (noticeId) {
                        localStorage.setItem(
                            dismissedKey(noticeId),
                            '1'
                        );
                    };

                root.querySelectorAll(
                    '[data-central-notice-id]'
                ).forEach(
                    function (notice) {
                        if (
                            isDismissed(
                                notice.dataset.centralNoticeId
                            )
                        ) {
                            notice.remove();
                        }
                    }
                );

                root.addEventListener(
                    'click',
                    function (event) {
                        const button =
                            event.target.closest(
                                '[data-es-dismiss-central-notice]'
                            );

                        if (!button) {
                            return;
                        }

                        const noticeId =
                            button.dataset
                                .esDismissCentralNotice;

                        dismiss(noticeId);

                        const notice =
                            button.closest(
                                '[data-central-notice-id]'
                            );

                        notice?.remove();

                        if (
                            window.esCentralRotationAdvanceV24
                        ) {
                            window
                                .esCentralRotationAdvanceV24();
                        }
                    }
                );

                const modal =
                    document.getElementById(
                        'esCentralImageModalV24'
                    );

                const modalImage =
                    document.getElementById(
                        'esCentralImagePreviewV24'
                    );

                const closeModal =
                    function () {
                        if (!modal) {
                            return;
                        }

                        modal.classList.remove(
                            'is-open'
                        );

                        modal.setAttribute(
                            'aria-hidden',
                            'true'
                        );

                        if (modalImage) {
                            modalImage.src = '';
                        }
                    };

                root.addEventListener(
                    'click',
                    function (event) {
                        const preview =
                            event.target.closest(
                                '[data-es-image-preview]'
                            );

                        if (!preview || !modal) {
                            return;
                        }

                        if (modalImage) {
                            modalImage.src =
                                preview.dataset
                                    .esImagePreview;
                        }

                        modal.classList.add(
                            'is-open'
                        );

                        modal.setAttribute(
                            'aria-hidden',
                            'false'
                        );
                    }
                );

                modal?.addEventListener(
                    'click',
                    function (event) {
                        if (
                            event.target === modal
                            || event.target.closest(
                                '.es-central-image-close-v24'
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
                        ) {
                            closeModal();
                        }
                    }
                );

                const slot =
                    document.getElementById(
                        'esCentralRotatingSlotV24'
                    );

                const dataNode =
                    document.getElementById(
                        'esCentralRotatingDataV24'
                    );

                if (!slot || !dataNode) {
                    return;
                }

                let notices = [];

                try {
                    notices =
                        JSON.parse(
                            dataNode.textContent || '[]'
                        );
                } catch (error) {
                    notices = [];
                }

                notices =
                    notices.filter(
                        function (notice) {
                            return !isDismissed(
                                notice.id
                            );
                        }
                    );

                let index = 0;
                let timer = null;

                const clearTimer =
                    function () {
                        if (timer) {
                            clearTimeout(timer);
                            timer = null;
                        }
                    };

                const escapeHtml =
                    function (value) {
                        const div =
                            document.createElement(
                                'div'
                            );

                        div.textContent =
                            value || '';

                        return div.innerHTML;
                    };

                const render =
                    function () {
                        clearTimer();

                        notices =
                            notices.filter(
                                function (notice) {
                                    return !isDismissed(
                                        notice.id
                                    );
                                }
                            );

                        if (!notices.length) {
                            slot.innerHTML = '';
                            return;
                        }

                        if (index >= notices.length) {
                            index = 0;
                        }

                        const notice =
                            notices[index];

                        const imageHtml =
                            notice.image_url
                                ? `
                                    <button
                                        type="button"
                                        class="es-central-notice-image-button-v24"
                                        data-es-image-preview="${escapeHtml(notice.image_url)}"
                                        aria-label="Preview notice image"
                                    >
                                        <img
                                            src="${escapeHtml(notice.image_url)}"
                                            alt=""
                                            class="es-central-notice-image-v24"
                                        >
                                    </button>
                                `
                                : '';

                        const closeHtml =
                            notice.dismissible
                                ? `
                                    <button
                                        type="button"
                                        class="es-central-notice-dismiss-v24"
                                        data-es-dismiss-central-notice="${notice.id}"
                                        aria-label="Dismiss notice"
                                    >
                                        &times;
                                    </button>
                                `
                                : '';

                        const titleHtml =
                            notice.title
                                ? `
                                    <h3 class="es-central-notice-title-v24">
                                        ${escapeHtml(notice.title)}
                                    </h3>
                                `
                                : '';

                        slot.innerHTML = `
                            <article
                                class="es-central-notice-v24"
                                data-central-notice-id="${notice.id}"
                                data-variant="${escapeHtml(notice.variant || 'info')}"
                            >
                                ${imageHtml}

                                <div class="es-central-notice-inner-v24">
                                    ${closeHtml}
                                    ${titleHtml}

                                    <div class="es-central-notice-message-v24">
                                        ${notice.message_html || ''}
                                    </div>
                                </div>
                            </article>
                        `;

                        if (
                            notices.length > 1
                            && !document.hidden
                        ) {
                            timer =
                                setTimeout(
                                    function () {
                                        index =
                                            (
                                                index + 1
                                            )
                                            % notices.length;

                                        render();
                                    },
                                    Math.max(
                                        3,
                                        Number(
                                            notice.rotation_seconds
                                            || 8
                                        )
                                    ) * 1000
                                );
                        }
                    };

                window.esCentralRotationAdvanceV24 =
                    function () {
                        clearTimer();

                        notices =
                            notices.filter(
                                function (notice) {
                                    return !isDismissed(
                                        notice.id
                                    );
                                }
                            );

                        if (notices.length) {
                            index =
                                index
                                % notices.length;
                        }

                        render();
                    };

                document.addEventListener(
                    'visibilitychange',
                    function () {
                        if (document.hidden) {
                            clearTimer();
                            return;
                        }

                        render();
                    }
                );

                render();
            })();
        </script>
    </section>
@endif
