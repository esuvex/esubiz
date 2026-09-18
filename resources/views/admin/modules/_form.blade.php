@php
    $editing = isset($module);
    $catalog = $editing ? $module->catalogProduct : null;
    $audience = old('audience', $catalog?->audience ?? 'both');

    $saasAvailable = old(
        'saas_available',
        in_array($audience, ['saas', 'both'], true) ? 1 : 0
    );

    $offServerAvailable = old(
        'off_server_available',
        in_array($audience, ['developer', 'both'], true) ? 1 : 0
    );

    $selectedWebsiteTypes = collect(
        old(
            'compatible_website_types',
            $editing ? ($module->compatible_website_types ?? []) : []
        )
    )->map(fn ($id) => (string) $id)->all();

    $selectedThemes = collect(
        old(
            'compatible_themes',
            $editing ? ($module->compatible_themes ?? []) : []
        )
    )->map(fn ($slug) => (string) $slug)->all();

    $coreConfig = old(
        'core_function_configuration',
        $editing ? ($module->core_function_configuration ?? []) : []
    );
@endphp

@if ($errors->any())
    <div class="alert alert-danger">
        <strong>Please check the highlighted fields.</strong>
        <ul class="mb-0 mt-2">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="card mb-4">
    <div class="card-header">
        <strong>Module Information</strong>
    </div>

    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Module Name</label>
                <input
                    type="text"
                    name="name"
                    class="form-control"
                    value="{{ old('name', $module->name ?? '') }}"
                    required
                >
            </div>

            <div class="col-md-6">
                <label class="form-label">Slug</label>
                <input
                    type="text"
                    name="slug"
                    class="form-control"
                    value="{{ old('slug', $module->slug ?? '') }}"
                    required
                >
            </div>

            <div class="col-md-4">
                <label class="form-label">Version</label>
                <input
                    type="text"
                    name="version"
                    class="form-control"
                    value="{{ old('version', $module->version ?? '1.0.0') }}"
                    required
                >
            </div>

            <div class="col-md-4">
                <label class="form-label">Module Type</label>
                <select name="type" class="form-select" required>
                    @foreach (['official', 'developer', 'private', 'core'] as $type)
                        <option
                            value="{{ $type }}"
                            @selected(old('type', $module->type ?? 'official') === $type)
                        >
                            {{ ucfirst($type) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-4">
                <label class="form-label">Sidebar Menu Name</label>
                <input
                    type="text"
                    name="sidebar_menu_name"
                    class="form-control"
                    placeholder="e.g. Store Management"
                    value="{{ old('sidebar_menu_name', $module->sidebar_menu_name ?? '') }}"
                    required
                >
            </div>

            <div class="col-12">
                <label class="form-label">Description</label>
                <textarea
                    name="description"
                    rows="4"
                    class="form-control"
                >{{ old('description', $module->description ?? '') }}</textarea>
            </div>

            <div class="col-md-6">
                <label class="form-label">Local Package Folder</label>
                <input
                    type="text"
                    name="package_name"
                    class="form-control"
                    placeholder="Search/select module package folder"
                    value="{{ old('package_name', $module->package_name ?? '') }}"
                    autocomplete="off"
                >
                <div class="form-text">
                    Select an existing module folder when developing directly on Central.
                </div>
            </div>

            <div class="col-md-6">
                <label class="form-label">Module Package</label>
                <input
                    type="file"
                    name="package_file"
                    class="form-control"
                    accept=".zip,application/zip"
                >
                <div class="form-text">
                    Upload a new ZIP release. Previous releases remain stored for version history,
                    while only the current published version is active.
                </div>
            </div>

            <div class="col-md-6">
                <label class="form-label">Preview Photo</label>
                <input
                    type="file"
                    name="preview_image"
                    id="module_preview_image"
                    class="form-control"
                    accept="image/png,image/jpeg,image/webp"
                >
                            <div class="form-text">
                                Recommended Marketplace preview: <strong>1600 × 1000 px</strong>
                                (8:5 landscape). The image may be automatically cropped for
                                Marketplace cards and responsive displays.
                            </div>

                <div class="mt-3">
                    <img
                        id="module_preview"
                        src=""
                        alt="Module preview"
                        style="display:none;max-width:260px;max-height:180px;object-fit:cover;border-radius:8px;"
                    >
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header">
        <strong>Deployment & Pricing</strong>
    </div>

    <div class="card-body">
        <p class="text-muted mb-3">
            Base prices use Central's current primary currency:
            <strong>{{ $primaryCurrency }}</strong>.
        </p>

        <div class="row g-4">
            <div class="col-md-6">
                <input type="hidden" name="saas_available" value="0">
                <div class="form-check form-switch mb-3">
                    <input
                        class="form-check-input"
                        type="checkbox"
                        name="saas_available"
                        value="1"
                        id="saas_available"
                        @checked((bool) $saasAvailable)
                    >
                    <label class="form-check-label" for="saas_available">
                        SaaS Available
                    </label>
                </div>

                <label class="form-label">
                    SaaS Price ({{ $primaryCurrency }})
                </label>
                <input
                    type="number"
                    step="0.01"
                    min="0"
                    name="saas_price"
                    class="form-control"
                    value="{{ old('saas_price', $saasPrice ?? '') }}"
                >

                <input type="hidden" name="saas_wizard_visible" value="0">
                <div class="form-check form-switch mt-3">
                    <input
                        class="form-check-input"
                        type="checkbox"
                        name="saas_wizard_visible"
                        value="1"
                        id="saas_wizard_visible"
                        @checked((bool) old('saas_wizard_visible', $module->saas_wizard_visible ?? true))
                    >
                    <label class="form-check-label" for="saas_wizard_visible">
                        Visible in SaaS Wizard
                    </label>
                </div>
            </div>

            <div class="col-md-6">
                <input type="hidden" name="off_server_available" value="0">
                <div class="form-check form-switch mb-3">
                    <input
                        class="form-check-input"
                        type="checkbox"
                        name="off_server_available"
                        value="1"
                        id="off_server_available"
                        @checked((bool) $offServerAvailable)
                    >
                    <label class="form-check-label" for="off_server_available">
                        Off-server Available
                    </label>
                </div>

                <label class="form-label">
                    Off-server Price ({{ $primaryCurrency }})
                </label>
                <input
                    type="number"
                    step="0.01"
                    min="0"
                    name="off_server_price"
                    class="form-control"
                    value="{{ old('off_server_price', $offServerPrice ?? '') }}"
                >

                <input type="hidden" name="off_server_wizard_visible" value="0">
                <div class="form-check form-switch mt-3">
                    <input
                        class="form-check-input"
                        type="checkbox"
                        name="off_server_wizard_visible"
                        value="1"
                        id="off_server_wizard_visible"
                        @checked((bool) old('off_server_wizard_visible', $module->off_server_wizard_visible ?? true))
                    >
                    <label class="form-check-label" for="off_server_wizard_visible">
                        Visible in Off-server Wizard
                    </label>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header">
        <strong>Website Type Compatibility</strong>
    </div>

    <div class="card-body">
        <div class="row">
            @forelse ($websiteTypes as $websiteType)
                <div class="col-md-4 mb-2">
                    <div class="form-check">
                        <input
                            class="form-check-input"
                            type="checkbox"
                            name="compatible_website_types[]"
                            value="{{ $websiteType->id }}"
                            id="website_type_{{ $websiteType->id }}"
                            @checked(in_array((string) $websiteType->id, $selectedWebsiteTypes, true))
                        >
                        <label
                            class="form-check-label"
                            for="website_type_{{ $websiteType->id }}"
                        >
                            {{ $websiteType->name }}
                        </label>
                    </div>
                </div>
            @empty
                <div class="text-muted">No active Website Types found.</div>
            @endforelse
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header">
        <strong>Theme Compatibility</strong>
    </div>

    <div class="card-body">
        <p class="text-muted">
            Only themes explicitly selected here will be compatible with this module.
        </p>

        <div class="row">
            @forelse ($themes as $theme)
                <div class="col-md-4 mb-2">
                    <div class="form-check">
                        <input
                            class="form-check-input"
                            type="checkbox"
                            name="compatible_themes[]"
                            value="{{ $theme->slug }}"
                            id="theme_{{ $theme->id }}"
                            @checked(in_array((string) $theme->slug, $selectedThemes, true))
                        >
                        <label
                            class="form-check-label"
                            for="theme_{{ $theme->id }}"
                        >
                            {{ $theme->name }}
                        </label>
                    </div>
                </div>
            @empty
                <div class="text-muted">
                    No Marketplace-ready themes are currently available.
                </div>
            @endforelse
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header">
        <strong>Core Functions</strong>
    </div>

    <div class="card-body">
        <p class="text-muted">
            These controls allow the module to integrate with available Core functions.
            Core limits and Add-on allowances remain authoritative.
        </p>

        @forelse ($coreMenus as $menu)
            @php
                $menuKey = $menu['key'] ?? '';
                $menuEnabled = (bool) data_get(
                    $coreConfig,
                    $menuKey . '.enabled',
                    false
                );
            @endphp

            <div class="border rounded p-3 mb-3">
                <div class="d-flex align-items-center justify-content-between">
                    <strong>{{ $menu['label'] ?? $menuKey }}</strong>

                    <div class="form-check form-switch">
                        <input type="hidden"
                               name="core_function_configuration[{{ $menuKey }}][enabled]"
                               value="0">

                        <input
                            class="form-check-input"
                            type="checkbox"
                            name="core_function_configuration[{{ $menuKey }}][enabled]"
                            value="1"
                            id="core_menu_{{ $menuKey }}"
                            @checked($menuEnabled)
                        >
                    </div>
                </div>

                @if (!empty($menu['children']))
                    <div class="mt-3 ps-2">
                        @foreach ($menu['children'] as $child)
                            @php
                                $childKey = $child['key'] ?? '';
                                $childEnabled = (bool) data_get(
                                    $coreConfig,
                                    $menuKey . '.children.' . $childKey,
                                    false
                                );
                            @endphp

                            <div class="form-check mb-2">
                                <input type="hidden"
                                       name="core_function_configuration[{{ $menuKey }}][children][{{ $childKey }}]"
                                       value="0">

                                <input
                                    class="form-check-input"
                                    type="checkbox"
                                    name="core_function_configuration[{{ $menuKey }}][children][{{ $childKey }}]"
                                    value="1"
                                    id="core_child_{{ $menuKey }}_{{ $childKey }}"
                                    @checked($childEnabled)
                                >

                                <label
                                    class="form-check-label"
                                    for="core_child_{{ $menuKey }}_{{ $childKey }}"
                                >
                                    {{ $child['label'] ?? $childKey }}
                                </label>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        @empty
            <div class="text-muted">No Core function integrations available.</div>
        @endforelse
    </div>
</div>

<div class="card mb-4">
    <div class="card-header">
        <strong>Marketplace Status</strong>
    </div>

    <div class="card-body">
        <div class="row g-3">
            @foreach ([
                'is_verified' => 'Verified',
                'is_featured' => 'Featured',
                'is_public' => 'Public',
                'is_active' => 'Active',
            ] as $field => $label)
                <div class="col-md-3">
                    <input type="hidden" name="{{ $field }}" value="0">

                    <div class="form-check form-switch">
                        <input
                            class="form-check-input"
                            type="checkbox"
                            name="{{ $field }}"
                            value="1"
                            id="{{ $field }}"
                            @checked((bool) old(
                                $field,
                                $field === 'is_featured'
                                    ? ($catalog?->is_featured ?? false)
                                    : (
                                        $field === 'is_public'
                                            ? ($catalog?->is_public ?? true)
                                            : ($module->{$field} ?? true)
                                    )
                            ))
                        >

                        <label class="form-check-label" for="{{ $field }}">
                            {{ $label }}
                        </label>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>

<div class="d-flex justify-content-end gap-2">
    <a href="{{ route('admin.modules.index') }}" class="btn btn-light">
        Cancel
    </a>

    <button type="submit" class="btn btn-primary">
        {{ $editing ? 'Save Module' : 'Create Module' }}
    </button>
</div>

<script>
document.addEventListener('change', function (event) {
    if (event.target.id !== 'module_preview_image') {
        return;
    }

    const file = event.target.files && event.target.files[0];
    const preview = document.getElementById('module_preview');

    if (!file || !preview) {
        return;
    }

    preview.src = URL.createObjectURL(file);
    preview.style.display = 'block';
});
</script>
