@props([
    'product' => 'Product',
    'mode' => 'folder',
    'folderName' => 'package_name',
    'folderValue' => '',
    'folderPlaceholder' => 'Search/select package folder',
    'uploadName' => 'package_file',
    'accept' => '.zip,application/zip',
    'help' => null,
])

@php
    $uid = 'esu-source-' . \Illuminate\Support\Str::random(8);
    $selectedMode = old('package_source_mode', $mode ?: 'folder');

    if (!in_array($selectedMode, ['folder', 'upload'], true)) {
        $selectedMode = 'folder';
    }
@endphp

<div
    id="{{ $uid }}"
    class="esu-product-source"
    data-default-mode="{{ $selectedMode }}"
>
    <input
        type="hidden"
        name="package_source_mode"
        value="{{ $selectedMode }}"
        class="js-esu-source-mode"
    >

    <div class="esu-source-heading">
        <div>
            <div class="esu-source-title">{{ $product }} Source</div>
            <div class="esu-source-copy">
                Select an existing Esubiz {{ strtolower($product) }} package or upload a ZIP.
            </div>
        </div>

        <div
            class="esu-source-toggle-row"
            role="group"
            aria-label="{{ $product }} package source"
        >
            <button
                type="button"
                class="esu-source-side-label js-esu-source-folder"
                data-mode="folder"
            >
                Search Folder
            </button>

            <button
                type="button"
                class="esu-source-switch js-esu-source-switch"
                aria-label="Switch {{ $product }} package source"
            >
                <span class="esu-source-knob"></span>
            </button>

            <button
                type="button"
                class="esu-source-side-label js-esu-source-upload"
                data-mode="upload"
            >
                Upload ZIP
            </button>
        </div>
    </div>

    <div class="esu-source-panel js-esu-folder-panel">
        <label class="esu-source-label">
            {{ $product }} Package Folder
        </label>

        <div class="esu-source-search">
            <span class="esu-source-search-icon">⌕</span>

            <input
                type="text"
                name="{{ $folderName }}"
                value="{{ old($folderName, $folderValue) }}"
                placeholder="{{ $folderPlaceholder }}"
                autocomplete="off"
                class="esu-source-input js-esu-folder-input"
            >
        </div>
    </div>

    <div class="esu-source-panel js-esu-upload-panel">
        <label class="esu-source-label">
            Upload {{ $product }} ZIP
        </label>

        <label class="esu-upload-box">
            <input
                type="file"
                name="{{ $uploadName }}"
                accept="{{ $accept }}"
                class="js-esu-upload-input"
            >

            <span class="esu-upload-icon">↑</span>

            <span>
                <strong>Choose {{ $product }} ZIP</strong>
                <small>Upload a valid universal Esubiz {{ strtolower($product) }} package.</small>
            </span>
        </label>
    </div>

    @if ($help)
        <div class="esu-source-help">{{ $help }}</div>
    @endif
</div>

@once
<style>
.esu-product-source{
    padding:20px;
    border:1px solid #e2e8f0;
    border-radius:16px;
    background:#fff;
}

.esu-source-heading{
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:18px;
    margin-bottom:20px;
}

.esu-source-title{
    color:#0f172a;
    font-size:14px;
    font-weight:750;
}

.esu-source-copy{
    margin-top:3px;
    color:#64748b;
    font-size:12px;
}

.esu-source-toggle-row{
    display:flex;
    align-items:center;
    gap:10px;
}

.esu-source-side-label{
    padding:0;
    border:0;
    background:transparent;
    color:#64748b;
    font-size:12px;
    font-weight:700;
    cursor:pointer;
    white-space:nowrap;
}

.esu-product-source[data-mode="folder"] .js-esu-source-folder{
    color:#334155;
}

.esu-product-source[data-mode="upload"] .js-esu-source-upload{
    color:#2563eb;
}

.esu-source-switch{
    position:relative;
    width:46px;
    height:25px;
    flex:none;
    padding:0;
    border:0;
    border-radius:999px;
    background:#94a3b8;
    cursor:pointer;
    transition:background .2s ease;
}

.esu-product-source[data-mode="upload"] .esu-source-switch{
    background:#2563eb;
}

.esu-source-knob{
    position:absolute;
    top:3px;
    left:3px;
    width:19px;
    height:19px;
    border-radius:50%;
    background:#fff;
    box-shadow:0 1px 4px rgba(15,23,42,.25);
    transition:transform .2s ease;
}

.esu-product-source[data-mode="upload"] .esu-source-knob{
    transform:translateX(21px);
}

.esu-source-panel{
    display:none;
}

.esu-product-source[data-mode="folder"] .js-esu-folder-panel,
.esu-product-source[data-mode="upload"] .js-esu-upload-panel{
    display:block;
}

.esu-source-label{
    display:block;
    margin-bottom:7px;
    color:#334155;
    font-size:12px;
    font-weight:700;
}

.esu-source-search{
    position:relative;
}

.esu-source-search-icon{
    position:absolute;
    top:50%;
    left:14px;
    color:#94a3b8;
    font-size:18px;
    transform:translateY(-50%);
    pointer-events:none;
}

.esu-source-input{
    width:100%;
    min-height:44px;
    padding:0 14px 0 42px;
    border:1px solid #cbd5e1;
    border-radius:11px;
    background:#fff;
    color:#0f172a;
    font-size:13px;
    outline:none;
}

.esu-source-input:focus{
    border-color:#60a5fa;
    box-shadow:0 0 0 3px rgba(37,99,235,.10);
}

.esu-upload-box{
    display:flex;
    align-items:center;
    gap:13px;
    min-height:78px;
    padding:16px;
    border:1px dashed #93c5fd;
    border-radius:12px;
    background:#eff6ff;
    cursor:pointer;
}

.esu-upload-box input{
    position:absolute;
    width:1px;
    height:1px;
    opacity:0;
    pointer-events:none;
}

.esu-upload-icon{
    display:flex;
    align-items:center;
    justify-content:center;
    width:38px;
    height:38px;
    flex:none;
    border-radius:10px;
    background:#2563eb;
    color:#fff;
    font-size:20px;
    font-weight:700;
}

.esu-upload-box strong{
    display:block;
    color:#1e3a8a;
    font-size:13px;
}

.esu-upload-box small{
    display:block;
    margin-top:3px;
    color:#64748b;
    font-size:11px;
}

.esu-source-help{
    margin-top:9px;
    color:#64748b;
    font-size:11px;
}

@media(max-width:767px){
    .esu-source-heading{
        align-items:stretch;
        flex-direction:column;
    }

    .esu-source-toggle-row{
        align-self:flex-start;
        flex-wrap:wrap;
        max-width:100%;
    }

    .esu-source-switch{
        width:46px;
        min-width:46px;
        max-width:46px;
        flex:0 0 46px;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.esu-product-source').forEach(function (root) {
        if (root.dataset.initialized === '1') {
            return;
        }

        root.dataset.initialized = '1';

        const hidden = root.querySelector('.js-esu-source-mode');
        const folderInput = root.querySelector('.js-esu-folder-input');
        const uploadInput = root.querySelector('.js-esu-upload-input');

        function setMode(mode) {
            mode = mode === 'upload' ? 'upload' : 'folder';

            root.dataset.mode = mode;
            hidden.value = mode;

            if (folderInput) {
                folderInput.disabled = mode !== 'folder';
            }

            if (uploadInput) {
                uploadInput.disabled = mode !== 'upload';
            }
        }

        root.querySelectorAll('.esu-source-side-label').forEach(function (button) {
            button.addEventListener('click', function () {
                setMode(button.dataset.mode);
            });
        });

        const toggle = root.querySelector('.js-esu-source-switch');

        if (toggle) {
            toggle.addEventListener('click', function () {
                setMode(root.dataset.mode === 'upload' ? 'folder' : 'upload');
            });
        }

        setMode(root.dataset.defaultMode || 'folder');
    });
});
</script>
@endonce
