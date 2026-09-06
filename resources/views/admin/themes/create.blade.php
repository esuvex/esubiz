
@extends('admin.layouts.app')

@section('content')

<style>
    .esubiz-theme-create-page {
        max-width: 1080px;
        margin: 0 auto;
        padding: 30px 28px 45px;
    }

    .esubiz-theme-create-back {
        display: inline-block;
        margin-bottom: 18px;
        color: #667085;
        font-size: 12px;
        font-weight: 700;
        text-decoration: none;
    }

    .esubiz-theme-create-head {
        margin-bottom: 24px;
    }

    .esubiz-theme-create-head h1 {
        margin: 0;
        color: #101828;
        font-size: 27px;
        font-weight: 800;
    }

    .esubiz-theme-create-head p {
        margin: 7px 0 0;
        color: #667085;
        font-size: 13px;
        line-height: 1.6;
    }

    .esubiz-theme-create-card {
        overflow: hidden;
        border: 1px solid #e4e9f0;
        border-radius: 18px;
        background: #fff;
        box-shadow: 0 14px 38px rgba(15,23,42,.055);
    }

    .esubiz-theme-create-card-head {
        padding: 24px 27px;
        border-bottom: 1px solid #eef1f5;
    }

    .esubiz-theme-create-card-head strong {
        display: block;
        color: #101828;
        font-size: 17px;
        font-weight: 800;
    }

    .esubiz-theme-create-card-head span {
        display: block;
        margin-top: 5px;
        color: #667085;
        font-size: 12px;
    }

    .esubiz-theme-create-body {
        padding: 27px;
    }

    .esubiz-theme-source-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 12px;
        margin-bottom: 24px;
    }

    .esubiz-theme-source {
        position: relative;
        margin: 0;
        cursor: pointer;
    }

    .esubiz-theme-source input {
        position: absolute;
        opacity: 0;
    }

    .esubiz-theme-source-box {
        display: block;
        padding: 17px;
        border: 1px solid #dfe5ee;
        border-radius: 12px;
        background: #fff;
    }

    .esubiz-theme-source input:checked + .esubiz-theme-source-box {
        border-color: #2563eb;
        background: #f7faff;
        box-shadow: 0 0 0 3px rgba(37,99,235,.08);
    }

    .esubiz-theme-source-box strong {
        display: block;
        color: #1d2939;
        font-size: 13px;
        font-weight: 800;
    }

    .esubiz-theme-source-box span {
        display: block;
        margin-top: 4px;
        color: #667085;
        font-size: 11.5px;
        line-height: 1.5;
    }

    .esubiz-theme-field {
        margin-bottom: 20px;
    }

    .esubiz-theme-field label {
        display: block;
        margin-bottom: 8px;
        color: #344054;
        font-size: 12px;
        font-weight: 800;
    }

    .esubiz-theme-field select,
    .esubiz-theme-field input[type="file"] {
        width: 100%;
        min-height: 45px;
        padding: 10px 12px;
        border: 1px solid #d5dce6;
        border-radius: 10px;
        background: #fff;
        color: #344054;
        font-size: 12px;
    }

    .esubiz-theme-help {
        margin-top: 7px;
        color: #7c8798;
        font-size: 11px;
    }

    .esubiz-theme-create-actions {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        padding-top: 6px;
    }

    .esubiz-theme-btn-primary {
        min-height: 42px;
        padding: 0 18px;
        border: 0;
        border-radius: 9px;
        background: #2563eb;
        color: #fff;
        font-size: 12px;
        font-weight: 800;
        cursor: pointer;
    }

    @media(max-width: 700px) {
        .esubiz-theme-source-grid {
            grid-template-columns: 1fr;
        }
    }
</style>


<div class="esubiz-theme-create-page">

    <a
        href="{{ route('admin.themes.index') }}"
        class="esubiz-theme-create-back"
    >
        ← Back to Themes
    </a>


    <div class="esubiz-theme-create-head">

        <h1>Add Theme</h1>

        <p>
            Register a universal Esubiz Core Theme package.
            Package facts come from theme.json; pricing and
            Marketplace settings are configured after registration.
        </p>

    </div>


    <div class="esubiz-theme-create-card">

        <div class="esubiz-theme-create-card-head">

            <strong>
                Theme Package
            </strong>

            <span>
                Choose an existing package from protected storage
                or upload a new Theme ZIP.
            </span>

        </div>


        <div class="esubiz-theme-create-body">

            <form
                method="POST"
                action="{{ route('admin.themes.packages.store') }}"
                enctype="multipart/form-data"
            >
                @csrf


                <div class="esubiz-theme-source-grid">

                    <label class="esubiz-theme-source">

                        <input
                            type="radio"
                            name="package_source"
                            value="storage"
                            id="theme-source-storage"
                            checked
                        >

                        <span class="esubiz-theme-source-box">

                            <strong>
                                Select from Storage
                            </strong>

                            <span>
                                Use a Theme ZIP already available in
                                protected Esubiz product storage.
                            </span>

                        </span>

                    </label>


                    <label class="esubiz-theme-source">

                        <input
                            type="radio"
                            name="package_source"
                            value="upload"
                            id="theme-source-upload"
                        >

                        <span class="esubiz-theme-source-box">

                            <strong>
                                Upload Theme Package
                            </strong>

                            <span>
                                Upload a new universal Esubiz Theme ZIP.
                            </span>

                        </span>

                    </label>

                </div>


                <div
                    class="esubiz-theme-field"
                    id="theme-storage-field"
                >

                    <label>
                        Theme Package in Storage
                    </label>

                    <select
                        name="storage_package"
                        id="theme-storage-package"
                        required
                    >

                        <option value="">
                            Select Theme Package
                        </option>

                        @foreach(
                            ($storageThemePackages ?? collect())
                            as $package
                        )

                            <option value="{{ $package['filename'] }}">

                                {{ $package['name'] }}

                                @if(!empty($package['version']))
                                    v{{ $package['version'] }}
                                @endif

                                — {{ $package['filename'] }}

                            </option>

                        @endforeach

                    </select>

                </div>


                <div
                    class="esubiz-theme-field"
                    id="theme-upload-field"
                    style="display:none;"
                >

                    <label>
                        Upload Theme ZIP
                    </label>

                    <input
                        type="file"
                        name="theme_package"
                        id="theme-upload-package"
                        accept=".zip,application/zip"
                        disabled
                    >

                    <div class="esubiz-theme-help">
                        theme.json must exist at the ZIP root.
                    </div>

                </div>


                <div class="esubiz-theme-create-actions">

                    <button
                        type="submit"
                        class="esubiz-theme-btn-primary"
                    >
                        Register Theme
                    </button>

                </div>

            </form>

        </div>

    </div>

</div>


<script>
document.addEventListener(
    'DOMContentLoaded',
    function () {

        const storage =
            document.getElementById(
                'theme-source-storage'
            );

        const upload =
            document.getElementById(
                'theme-source-upload'
            );

        const storageField =
            document.getElementById(
                'theme-storage-field'
            );

        const uploadField =
            document.getElementById(
                'theme-upload-field'
            );

        const storageInput =
            document.getElementById(
                'theme-storage-package'
            );

        const uploadInput =
            document.getElementById(
                'theme-upload-package'
            );


        function sync() {

            const isUpload =
                upload.checked;

            storageField.style.display =
                isUpload
                    ? 'none'
                    : 'block';

            uploadField.style.display =
                isUpload
                    ? 'block'
                    : 'none';

            storageInput.disabled =
                isUpload;

            storageInput.required =
                !isUpload;

            uploadInput.disabled =
                !isUpload;

            uploadInput.required =
                isUpload;
        }


        storage.addEventListener(
            'change',
            sync
        );

        upload.addEventListener(
            'change',
            sync
        );

        sync();
    }
);
</script>

@endsection
