{{-- ESUBIZ_INTERNAL_MEDIA_ON_DEMAND_V1 --}}
@props([
    'name',
    'label' => 'Photo',
    'currentUrl' => null,
    'removeName' => null,
    'recommendedWidth' => null,
    'recommendedHeight' => null,
    'maxMb' => 5,
    'storageScope' => 'workspace',
])

@php
    $removeField = $removeName ?: 'remove_' . $name;
@endphp

<div
    class="rounded-2xl border border-slate-200 bg-white p-4"
    data-esubiz-image-upload
>
    <div class="flex items-start justify-between gap-3">
        <div>
            <div class="text-xs font-black text-slate-700">
                {{ $label }}
            </div>

            @if($recommendedWidth && $recommendedHeight)
                <div class="mt-1 text-[11px] font-bold text-blue-600">
                    Recommended:
                    {{ $recommendedWidth }} × {{ $recommendedHeight }} px
                </div>
            @endif

            <div class="mt-1 text-[10px] text-slate-400">
                JPG, PNG or WEBP · Maximum {{ $maxMb }} MB
            </div>
        </div>

        <div class="rounded-full bg-slate-100 px-3 py-1 text-[10px] font-black uppercase text-slate-500">
            {{ $storageScope === 'central' ? 'Central Storage' : ($storageScope === 'tenant' ? 'Tenant Storage' : 'Workspace Storage') }}
        </div>
    </div>

    <div class="mt-4 flex flex-col gap-4 sm:flex-row sm:items-center">

        <div class="flex h-28 w-28 shrink-0 items-center justify-center overflow-hidden rounded-2xl border border-dashed border-slate-300 bg-slate-50">

            <x-media.image
    src="{{ $currentUrl ?: '' }}"
    alt="{{ $label }} preview"
    class="{{ $currentUrl ? '' : 'hidden' }} h-full w-full object-cover"
    data-image-preview
/>

            <span
                data-image-placeholder
                class="{{ $currentUrl ? 'hidden' : '' }} px-2 text-center text-[10px] font-bold text-slate-400"
            >
                Image preview
            </span>

        </div>

        <div class="min-w-0 flex-1">

            <input
                type="file"
                name="{{ $name }}"
                accept="image/jpeg,image/png,image/webp"
                data-image-input
                data-max-bytes="{{ $maxMb * 1024 * 1024 }}"
                class="block w-full rounded-xl border border-slate-200 bg-white px-3 py-3 text-sm"
            >

            <input
                type="hidden"
                name="{{ $removeField }}"
                value="0"
                data-image-remove-input
            >

            <div class="mt-3 flex flex-wrap gap-2">

                <button
                    type="button"
                    data-image-clear
                    class="hidden rounded-lg border border-slate-200 px-3 py-2 text-xs font-black text-slate-600"
                >
                    Clear New Photo
                </button>

                <button
                    type="button"
                    data-image-delete
                    class="{{ $currentUrl ? '' : 'hidden' }} rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-xs font-black text-red-600"
                >
                    Delete Photo
                </button>

            </div>

        </div>
    </div>
</div>

@once
<script data-esubiz-image-upload-script>
document.addEventListener('DOMContentLoaded', function () {

    document.querySelectorAll('[data-esubiz-image-upload]').forEach(function (root) {

        const input = root.querySelector('[data-image-input]');
        const preview = root.querySelector('[data-image-preview]');
        const placeholder = root.querySelector('[data-image-placeholder]');
        const removeInput = root.querySelector('[data-image-remove-input]');
        const deleteButton = root.querySelector('[data-image-delete]');
        const clearButton = root.querySelector('[data-image-clear]');

        if (!input || !preview) {
            return;
        }

        const originalUrl = preview.getAttribute('src') || '';
        let objectUrl = null;

        function render(url) {
            if (url) {
                preview.src = url;
                preview.classList.remove('hidden');
                placeholder?.classList.add('hidden');
            } else {
                preview.removeAttribute('src');
                preview.classList.add('hidden');
                placeholder?.classList.remove('hidden');
            }
        }

        input.addEventListener('change', function () {

            const file = input.files && input.files[0];

            if (!file) {
                return;
            }

            const max = Number(input.dataset.maxBytes || 0);

            if (max && file.size > max) {
                alert('The selected photo is larger than the allowed file size.');
                input.value = '';
                return;
            }

            if (!file.type.startsWith('image/')) {
                alert('Please select a valid image.');
                input.value = '';
                return;
            }

            if (objectUrl) {
                URL.revokeObjectURL(objectUrl);
            }

            objectUrl = URL.createObjectURL(file);

            render(objectUrl);

            if (removeInput) {
                removeInput.value = '0';
            }

            clearButton?.classList.remove('hidden');
            deleteButton?.classList.remove('hidden');
        });

        clearButton?.addEventListener('click', function () {

            input.value = '';

            if (objectUrl) {
                URL.revokeObjectURL(objectUrl);
                objectUrl = null;
            }

            render(originalUrl);

            if (removeInput) {
                removeInput.value = '0';
            }

            clearButton.classList.add('hidden');

            if (!originalUrl) {
                deleteButton?.classList.add('hidden');
            }
        });

        deleteButton?.addEventListener('click', function () {

            input.value = '';

            if (objectUrl) {
                URL.revokeObjectURL(objectUrl);
                objectUrl = null;
            }

            render('');

            if (removeInput) {
                removeInput.value = '1';
            }

            clearButton?.classList.add('hidden');
            deleteButton.classList.add('hidden');
        });

    });

});
</script>
@endonce
