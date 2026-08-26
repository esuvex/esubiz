@once
<div
    data-global-settings-save-status
    hidden
    class="
        fixed
        bottom-6
        right-6
        z-[200]
        rounded-xl
        border
        border-slate-200
        bg-white
        px-5
        py-3
        text-sm
        font-black
        shadow-xl
    "
>
    Saved
</div>

<script data-global-settings-autosave>
document.addEventListener(
    'DOMContentLoaded',
    function () {

        const status =
            document.querySelector(
                '[data-global-settings-save-status]'
            );

        let timer = null;


        function showStatus(
            text,
            type = 'saving'
        ) {

            if (!status) {
                return;
            }

            if (timer) {
                clearTimeout(timer);
            }

            status.textContent =
                text;

            status.hidden =
                false;

            status.classList.remove(
                'text-blue-600',
                'text-emerald-600',
                'text-red-600',
                'border-blue-200',
                'border-emerald-200',
                'border-red-200'
            );

            if (type === 'saving') {
                status.classList.add(
                    'text-blue-600',
                    'border-blue-200'
                );
            } else if (type === 'saved') {
                status.classList.add(
                    'text-emerald-600',
                    'border-emerald-200'
                );
            } else {
                status.classList.add(
                    'text-red-600',
                    'border-red-200'
                );
            }
        }


        function hideStatusLater() {

            timer =
                setTimeout(
                    function () {

                        if (status) {
                            status.hidden =
                                true;
                        }

                    },
                    1800
                );
        }


        async function saveField(
            element
        ) {

            const endpoint =
                element.getAttribute(
                    'data-autosave-url'
                );

            if (!endpoint) {
                return;
            }


            const field =
                element.getAttribute(
                    'data-autosave-field'
                )
                || element.name;

            if (!field) {
                return;
            }


            const csrf =
                document.querySelector(
                    'meta[name="csrf-token"]'
                )?.getAttribute(
                    'content'
                )
                || document.querySelector(
                    'input[name="_token"]'
                )?.value
                || '';


            let value = null;

            if (
                element.type === 'checkbox'
            ) {
                value =
                    element.checked
                        ? 1
                        : 0;
            } else if (
                element.type === 'radio'
            ) {

                if (!element.checked) {
                    return;
                }

                value =
                    element.value;

            } else {
                value =
                    element.value;
            }


            const previous =
                element.type === 'checkbox'
                    ? !element.checked
                    : element.dataset
                        .autosavePrevious;


            element.disabled =
                true;


            showStatus(
                'Saving...',
                'saving'
            );


            try {

                const response =
                    await fetch(
                        endpoint,
                        {
                            method:
                                'POST',

                            credentials:
                                'same-origin',

                            headers: {
                                'Content-Type':
                                    'application/json',

                                'Accept':
                                    'application/json',

                                'X-CSRF-TOKEN':
                                    csrf,

                                'X-Requested-With':
                                    'XMLHttpRequest',
                            },

                            body:
                                JSON.stringify({
                                    field:
                                        field,

                                    value:
                                        value,

                                    type:
                                        element.getAttribute(
                                            'data-autosave-type'
                                        )
                                        || undefined,

                                    id:
                                        element.getAttribute(
                                            'data-autosave-id'
                                        )
                                        || undefined,
                                }),
                        }
                    );


                let data = {};

                try {
                    data =
                        await response.json();
                } catch (_) {
                    data = {};
                }


                if (
                    !response.ok
                    || data.success === false
                ) {
                    throw new Error(
                        data.message
                        || 'Save failed.'
                    );
                }


                element.dataset
                    .autosavePrevious =
                    String(value);


                showStatus(
                    'Saved',
                    'saved'
                );


                hideStatusLater();


                element.dispatchEvent(
                    new CustomEvent(
                        'esubiz:autosave-saved',
                        {
                            bubbles: true,
                            detail: data,
                        }
                    )
                );


            } catch (error) {

                if (
                    element.type
                    === 'checkbox'
                ) {
                    element.checked =
                        !!previous;
                } else if (
                    previous !== undefined
                ) {
                    element.value =
                        previous;
                }


                showStatus(
                    'Could not save',
                    'error'
                );


                hideStatusLater();


                element.dispatchEvent(
                    new CustomEvent(
                        'esubiz:autosave-failed',
                        {
                            bubbles: true,
                            detail: {
                                error:
                                    error.message,
                            },
                        }
                    )
                );


            } finally {

                element.disabled =
                    false;
            }
        }


        document.querySelectorAll(
            '[data-autosave-url]'
        ).forEach(
            function (element) {

                element.dataset
                    .autosavePrevious =
                    element.type === 'checkbox'
                        ? (
                            element.checked
                                ? '1'
                                : '0'
                        )
                        : element.value;


                const eventName =
                    (
                        element.tagName
                            === 'SELECT'
                        || element.type
                            === 'checkbox'
                        || element.type
                            === 'radio'
                    )
                        ? 'change'
                        : 'blur';


                element.addEventListener(
                    eventName,
                    function () {

                        saveField(
                            element
                        );
                    }
                );
            }
        );

    }
);
</script>
@endonce
