<style data-theme-visibility-toggle-css>
    .theme-visibility-toggle {
        display: inline-grid;
        grid-template-columns: 1fr 1fr;
        width: 132px;
        height: 38px;
        border-radius: 999px;
        overflow: hidden;
        position: relative;
        border: 1px solid rgb(226 232 240);
        background: rgb(241 245 249);
        cursor: pointer;
        user-select: none;
    }

    .theme-visibility-toggle input {
        position: absolute;
        opacity: 0;
        pointer-events: none;
    }

    .theme-visibility-toggle .toggle-half {
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
        z-index: 2;
        font-size: 11px;
        font-weight: 900;
        transition: color .18s ease;
    }

    .theme-visibility-toggle .toggle-hide {
        color: rgb(185 28 28);
    }

    .theme-visibility-toggle .toggle-show {
        color: rgb(21 128 61);
    }

    .theme-visibility-toggle::before {
        content: '';
        position: absolute;
        left: 3px;
        top: 3px;
        width: calc(50% - 3px);
        height: 30px;
        border-radius: 999px;
        background: rgb(220 38 38);
        transition:
            transform .2s ease,
            background .2s ease;
        z-index: 1;
    }

    .theme-visibility-toggle:has(input:checked)::before {
        transform: translateX(100%);
        background: rgb(22 163 74);
    }

    .theme-visibility-toggle:not(:has(input:checked))
        .toggle-hide,
    .theme-visibility-toggle:has(input:checked)
        .toggle-show {
        color: white;
    }
</style>

<template id="featureTemplate">
        <div class="repeatable-feature rounded-2xl border border-slate-200 bg-slate-50 p-5">

            <div class="flex items-center justify-between">
                <div class="font-black text-slate-900">
                    Feature
                </div>

                <button
                    type="button"
                    class="remove-repeatable text-xs font-black text-red-600"
                >
                    Delete
                </button>
            </div>

            <div class="mt-4 grid gap-5 lg:grid-cols-[1fr_260px]">

                <div class="space-y-4">

                    <input
                        type="text"
                        data-field="title"
                        placeholder="Feature title"
                        class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm"
                    >

                    <textarea
                        data-field="text"
                        rows="4"
                        placeholder="Feature description"
                        class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm"
                    ></textarea>

                </div>

                <div>
                    <div class="text-xs font-bold text-slate-500">
                        Feature Image
                    </div>

                    <div class="mt-1 text-[11px] text-slate-400">
                        Recommended: 800 × 520 px.
                    </div>

                    <input
                        type="file"
                        data-field="image"
                        accept="image/*"
                        class="mt-3 block w-full text-xs"
                    >
                </div>

            </div>

        </div>
    </template>

<template id="testimonialTemplate">
        <div class="repeatable-testimonial rounded-2xl border border-slate-200 bg-slate-50 p-5">

            <div class="flex justify-end">
                <button
                    type="button"
                    class="remove-repeatable text-xs font-black text-red-600"
                >
                    Delete
                </button>
            </div>

            <div class="mt-3 grid gap-4">

                <div class="grid gap-4 md:grid-cols-2">

                    <input
                        type="text"
                        data-field="name"
                        placeholder="Customer name"
                        class="rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm"
                    >

                    <input
                        type="text"
                        data-field="role"
                        placeholder="Role / company"
                        class="rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm"
                    >

                </div>

                <textarea
                    data-field="text"
                    rows="4"
                    placeholder="Testimonial"
                    class="rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm"
                ></textarea>

                <div>
                    <div class="text-xs font-bold text-slate-500">
                        Customer Photo
                    </div>

                    <div class="mt-1 text-[11px] text-slate-400">
                        Recommended: 160 × 160 px square.
                    </div>

                    <input
                        type="file"
                        data-field="photo"
                        accept="image/*"
                        class="mt-3 block w-full text-xs"
                    >
                </div>

            </div>

        </div>
    </template>

<script>
        document.addEventListener(
            'DOMContentLoaded',
            function () {

                function attachDeleteButtons(root) {
                    root
                        .querySelectorAll(
                            '.remove-repeatable'
                        )
                        .forEach(function (button) {
                            button.onclick =
                                function () {
                                    button
                                        .closest(
                                            '.repeatable-feature, .repeatable-testimonial, .repeatable-footer-link, .repeatable-footer-social'
                                        )
                                        ?.remove();
                                };
                        });
                }


                function addRepeatable(
                    listId,
                    templateId,
                    prefix
                ) {
                    const list =
                        document.getElementById(
                            listId
                        );

                    const template =
                        document.getElementById(
                            templateId
                        );

                    const index =
                        Date.now().toString()
                        + Math.floor(
                            Math.random() * 1000
                        );

                    const fragment =
                        template.content.cloneNode(
                            true
                        );

                    fragment
                        .querySelectorAll(
                            '[data-field]'
                        )
                        .forEach(function (field) {

                            const key =
                                field.dataset.field;

                            field.name =
                                prefix
                                + '['
                                + index
                                + ']['
                                + key
                                + ']';
                        });

                    list.appendChild(fragment);

                    attachDeleteButtons(list);
                }


                document
                    .getElementById('addFeature')
                    ?.addEventListener(
                        'click',
                        function () {
                            addRepeatable(
                                'featureList',
                                'featureTemplate',
                                'features'
                            );
                        }
                    );


                document
                    .getElementById(
                        'addTestimonial'
                    )
                    ?.addEventListener(
                        'click',
                        function () {
                            addRepeatable(
                                'testimonialList',
                                'testimonialTemplate',
                                'testimonials'
                            );
                        }
                    );


                document
                    .getElementById(
                        'addFooterSocial'
                    )
                    ?.addEventListener(
                        'click',
                        function () {
                            addRepeatable(
                                'footerSocialList',
                                'footerSocialTemplate',
                                'footer_socials'
                            );
                        }
                    );


                document
                    .getElementById(
                        'addFooterLink'
                    )
                    ?.addEventListener(
                        'click',
                        function () {
                            addRepeatable(
                                'footerLinkList',
                                'footerLinkTemplate',
                                'footer_links'
                            );
                        }
                    );


                /*
                 * Instant Theme image previews.
                 *
                 * Works for:
                 * logo
                 * favicon
                 * hero
                 * about
                 * footer logo/background
                 * feature images
                 * testimonial photos
                 *
                 * The preview is local only until Save Theme is clicked.
                 */
                function installThemeImagePreviews(root) {
                    (root || document)
                        .querySelectorAll(
                            'input[type="file"][accept*="image"]'
                        )
                        .forEach(function (input) {

                            if (
                                input.dataset.themePreviewReady
                                === '1'
                            ) {
                                return;
                            }

                            input.dataset.themePreviewReady = '1';

                            input.addEventListener(
                                'change',
                                function () {

                                    const file =
                                        input.files
                                        && input.files[0];

                                    if (!file) {
                                        return;
                                    }

                                    if (
                                        !file.type
                                            .startsWith('image/')
                                    ) {
                                        return;
                                    }

                                    let container =
                                        input.parentElement;

                                    if (!container) {
                                        return;
                                    }

                                    let preview =
                                        container.querySelector(
                                            '[data-theme-upload-preview]'
                                        );

                                    if (!preview) {
                                        preview =
                                            document.createElement(
                                                'img'
                                            );

                                        preview.setAttribute(
                                            'data-theme-upload-preview',
                                            '1'
                                        );

                                        preview.alt =
                                            'Selected image preview';

                                        preview.className =
                                            input.name === 'favicon'
                                                ? 'mt-3 h-14 w-14 rounded-xl object-cover'
                                                : (
                                                    input.name
                                                        && input.name.includes(
                                                            '[photo]'
                                                        )
                                                        ? 'mt-3 h-16 w-16 rounded-full object-cover'
                                                        : 'mt-3 max-h-56 w-full rounded-xl object-contain bg-white'
                                                );

                                        input.insertAdjacentElement(
                                            'beforebegin',
                                            preview
                                        );
                                    }

                                    if (
                                        preview.dataset.objectUrl
                                    ) {
                                        URL.revokeObjectURL(
                                            preview.dataset.objectUrl
                                        );
                                    }

                                    const objectUrl =
                                        URL.createObjectURL(file);

                                    preview.dataset.objectUrl =
                                        objectUrl;

                                    preview.src =
                                        objectUrl;
                                }
                            );
                        });
                }

                installThemeImagePreviews(
                    document
                );

                /*
                 * New repeatable Feature/Testimonial rows are inserted
                 * dynamically, so observe the form and attach preview
                 * behaviour to newly-created image inputs too.
                 */
                const themePreviewObserver =
                    new MutationObserver(
                        function (mutations) {
                            mutations.forEach(
                                function (mutation) {
                                    mutation.addedNodes
                                        .forEach(
                                            function (node) {
                                                if (
                                                    node.nodeType
                                                    !== 1
                                                ) {
                                                    return;
                                                }

                                                installThemeImagePreviews(
                                                    node
                                                );
                                            }
                                        );
                                }
                            );
                        }
                    );

                themePreviewObserver.observe(
                    document,
                    {
                        childList: true,
                        subtree: true
                    }
                );

                function syncDeviceGroup(
                    groupName,
                    hiddenId
                ) {
                    const group =
                        document.querySelector(
                            `[data-device-group="${groupName}"]`
                        );

                    const hidden =
                        document.getElementById(
                            hiddenId
                        );

                    if (!group || !hidden) {
                        return;
                    }

                    const sync =
                        function () {
                            hidden.value =
                                Array.from(
                                    group.querySelectorAll(
                                        'input[type="checkbox"]:checked'
                                    )
                                )
                                .map(
                                    input =>
                                        input.value
                                )
                                .join(',');
                        };

                    group
                        .querySelectorAll(
                            'input[type="checkbox"]'
                        )
                        .forEach(
                            input =>
                                input.addEventListener(
                                    'change',
                                    sync
                                )
                        );

                    sync();
                }

                syncDeviceGroup(
                    'whatsapp',
                    'whatsappDevicesValue'
                );

                syncDeviceGroup(
                    'liveChat',
                    'liveChatDevicesValue'
                );

                syncDeviceGroup(
                    'backToTop',
                    'backToTopDevicesValue'
                );

                /*
                 * Theme photo detach controls.
                 *
                 * The Media Library file is NOT physically deleted.
                 * Only this theme reference is removed.
                 */
                document
                    .querySelectorAll(
                        '[data-theme-remove-photo]'
                    )
                    .forEach(
                        function (button) {

                            button.addEventListener(
                                'click',
                                function () {

                                    const inputName =
                                        button.getAttribute(
                                            'data-theme-remove-photo'
                                        );

                                    const hidden =
                                        document.querySelector(
                                            `[data-theme-remove-input="${inputName}"]`
                                        );

                                    if (hidden) {
                                        hidden.value = '1';
                                    }

                                    const container =
                                        button.parentElement;

                                    container
                                        ?.querySelectorAll(
                                            'img'
                                        )
                                        .forEach(
                                            function (image) {
                                                image.style.display =
                                                    'none';
                                            }
                                        );

                                    button.textContent =
                                        'Photo will be removed';

                                    button.disabled =
                                        true;
                                }
                            );
                        }
                    );


                document
                    .querySelectorAll(
                        '[data-repeatable-image-remove]'
                    )
                    .forEach(
                        function (button) {

                            button.addEventListener(
                                'click',
                                function () {

                                    const card =
                                        button.closest(
                                            '.repeatable-feature'
                                        );

                                    const hidden =
                                        card?.querySelector(
                                            '[data-feature-remove-input]'
                                        );

                                    if (hidden) {
                                        hidden.value = '1';
                                    }

                                    card
                                        ?.querySelectorAll(
                                            'img'
                                        )
                                        .forEach(
                                            image =>
                                                image.style.display =
                                                    'none'
                                        );

                                    button.textContent =
                                        'Photo will be removed';

                                    button.disabled =
                                        true;
                                }
                            );
                        }
                    );


                document
                    .querySelectorAll(
                        '[data-repeatable-photo-remove]'
                    )
                    .forEach(
                        function (button) {

                            button.addEventListener(
                                'click',
                                function () {

                                    const card =
                                        button.closest(
                                            '.repeatable-testimonial'
                                        );

                                    const hidden =
                                        card?.querySelector(
                                            '[data-testimonial-remove-input]'
                                        );

                                    if (hidden) {
                                        hidden.value = '1';
                                    }

                                    card
                                        ?.querySelectorAll(
                                            'img'
                                        )
                                        .forEach(
                                            image =>
                                                image.style.display =
                                                    'none'
                                        );

                                    button.textContent =
                                        'Photo will be removed';

                                    button.disabled =
                                        true;
                                }
                            );
                        }
                    );


                document.documentElement
                    .setAttribute(
                        'data-theme-delete-contract-installed',
                        '1'
                    );


                attachDeleteButtons(
                    document
                );

            }
        );
    </script>
