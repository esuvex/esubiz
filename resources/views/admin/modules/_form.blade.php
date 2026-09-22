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

    $selectedWebsiteTypes = collect(old(
        'compatible_website_types',
        $editing ? ($module->compatible_website_types ?? []) : []
    ))->map(fn ($id) => (string) $id)->all();

    $selectedThemes = collect(old(
        'compatible_themes',
        $editing ? ($module->compatible_themes ?? []) : []
    ))->map(fn ($slug) => (string) $slug)->all();

    $selectedMarketplaceCategories = collect(old(
        'marketplace_category_ids',
        $editing
            ? ($catalog?->marketplaceCategories()
                ->pluck('marketplace_categories.id')
                ->all() ?? [])
            : []
    ))->map(fn ($id) => (string) $id)->all();

    $coreConfig = old(
        'core_function_configuration',
        $editing ? ($module->core_function_configuration ?? []) : []
    );

    $selectedBillingInterval = (int) old(
        'saas_billing_interval',
        $saasBillingInterval ?? 1
    );

    $selectedBillingPeriod = old(
        'saas_billing_period',
        $saasBillingPeriod ?? 'monthly'
    );

    $billingUnit = match($selectedBillingPeriod) {
        'daily' => 'Day',
        'weekly' => 'Week',
        'yearly' => 'Year',
        default => 'Month',
    };
@endphp

<style>
    .esu-module-form{max-width:1440px;margin:0 auto;color:#0f172a}
    .esu-module-form *{box-sizing:border-box}
    .esu-module-card{background:#fff;border:1px solid #e2e8f0;border-radius:20px;box-shadow:0 8px 28px rgba(15,23,42,.055);margin-bottom:22px;overflow:hidden}
    .esu-module-card:has(.esu-multi){overflow:visible;position:relative;z-index:20}
    .esu-module-card:has(.esu-multi.open){z-index:200}
    .esu-module-head{padding:22px 24px;border-bottom:1px solid #eef2f7;display:flex;align-items:flex-start;justify-content:space-between;gap:18px}
    .esu-module-head h3{font-size:16px;font-weight:800;margin:0;color:#0f172a}
    .esu-module-head p{font-size:12px;color:#64748b;margin:5px 0 0;line-height:1.55}
    .esu-module-body{padding:24px}
    .esu-grid{display:grid;grid-template-columns:repeat(12,minmax(0,1fr));gap:18px}
    .esu-col-12{grid-column:span 12}.esu-col-8{grid-column:span 8}.esu-col-6{grid-column:span 6}.esu-col-4{grid-column:span 4}.esu-col-3{grid-column:span 3}
    .esu-field label{display:block;font-size:11px;font-weight:800;color:#475569;margin:0 0 8px;text-transform:uppercase;letter-spacing:.045em}
    .esu-field input[type=text],.esu-field input[type=number],.esu-field input[type=file],.esu-field textarea,.esu-field select{width:100%;border:1px solid #dbe3ee;border-radius:12px;background:#fff;color:#0f172a;padding:11px 13px;font-size:13px;outline:none;transition:.18s}
    .esu-field input:focus,.esu-field textarea:focus,.esu-field select:focus{border-color:#2563eb;box-shadow:0 0 0 3px rgba(37,99,235,.09)}
    .esu-field textarea{min-height:110px;resize:vertical}
    .esu-help{font-size:11px;color:#94a3b8;margin-top:7px;line-height:1.5}
    .esu-currency{display:flex;align-items:stretch}
    .esu-currency-code{display:flex;align-items:center;padding:0 14px;border:1px solid #dbe3ee;border-right:0;border-radius:12px 0 0 12px;background:#f8fafc;font-size:12px;font-weight:900;color:#334155}
    .esu-currency input{border-radius:0 12px 12px 0!important}
    .esu-deployment{border:1px solid #e2e8f0;border-radius:16px;padding:18px;height:100%;background:#fbfdff}
    .esu-deployment-title{display:flex;align-items:center;justify-content:space-between;gap:15px;margin-bottom:18px}
    .esu-deployment-title strong{font-size:14px;color:#0f172a}
    .esu-switch-row{display:flex;align-items:center;justify-content:space-between;gap:16px;padding:13px 0;border-bottom:1px solid #eef2f7}
    .esu-switch-row:last-child{border-bottom:0}
    .esu-switch-copy strong{display:block;font-size:12px;color:#334155}
    .esu-switch-copy span{display:block;font-size:10px;color:#94a3b8;margin-top:3px}
    .esu-toggle{position:relative;display:inline-block;width:42px;height:24px;flex:0 0 auto}
    .esu-toggle input{opacity:0;width:0;height:0}
    .esu-slider{position:absolute;inset:0;background:#cbd5e1;border-radius:999px;cursor:pointer;transition:.2s}
    .esu-slider:before{content:"";position:absolute;width:18px;height:18px;left:3px;top:3px;background:#fff;border-radius:50%;box-shadow:0 1px 3px rgba(0,0,0,.18);transition:.2s}
    .esu-toggle input:checked + .esu-slider{background:#2563eb}
    .esu-toggle input:checked + .esu-slider:before{transform:translateX(18px)}
    .esu-billing-preview{margin-top:14px;border-radius:12px;background:#eff6ff;border:1px solid #dbeafe;padding:12px 14px;font-size:11px;color:#1e40af}
    .esu-billing-preview strong{font-size:12px}
    .esu-multi{position:relative;z-index:1}
    .esu-multi.open{z-index:500}
    .esu-multi-trigger{width:100%;min-height:46px;border:1px solid #dbe3ee;border-radius:12px;background:#fff;padding:10px 13px;display:flex;align-items:center;justify-content:space-between;gap:12px;cursor:pointer;text-align:left;transition:.18s}
    .esu-multi-trigger:hover{border-color:#cbd5e1}
    .esu-multi.open .esu-multi-trigger{border-color:#2563eb;box-shadow:0 0 0 3px rgba(37,99,235,.09)}
    .esu-multi-value{min-width:0;display:flex;align-items:center;gap:8px;color:#64748b;font-size:12px}
    .esu-multi-value.has-selection{color:#0f172a;font-weight:700}
    .esu-multi-count{display:none;align-items:center;justify-content:center;min-width:22px;height:22px;padding:0 7px;border-radius:999px;background:#eff6ff;color:#2563eb;font-size:10px;font-weight:900}
    .esu-multi-value.has-selection .esu-multi-count{display:inline-flex}
    .esu-multi-arrow{width:8px;height:8px;border-right:2px solid #64748b;border-bottom:2px solid #64748b;transform:rotate(45deg) translateY(-2px);transition:.18s;flex:0 0 auto}
    .esu-multi.open .esu-multi-arrow{transform:rotate(225deg) translate(-2px,-2px)}
    .esu-multi-menu{display:none;position:absolute;left:0;right:0;top:calc(100% + 7px);z-index:1000;background:#fff;border:1px solid #dbe3ee;border-radius:14px;box-shadow:0 18px 45px rgba(15,23,42,.18);overflow:hidden}
    .esu-multi.open .esu-multi-menu{display:block}
    .esu-select-search{padding:11px;border-bottom:1px solid #eef2f7;background:#f8fafc}
    .esu-select-search input{width:100%;border:1px solid #dbe3ee;border-radius:10px;padding:9px 11px;font-size:12px;outline:none;background:#fff}
    .esu-select-search input:focus{border-color:#2563eb;box-shadow:0 0 0 3px rgba(37,99,235,.08)}
    .esu-options{max-height:240px;overflow-y:auto;padding:7px}
    .esu-option{display:flex;align-items:center;gap:10px;padding:9px 10px;border-radius:9px;font-size:12px;color:#334155;cursor:pointer;margin:0}
    .esu-option:hover{background:#f8fafc}
    .esu-option input{width:16px;height:16px;accent-color:#2563eb;flex:0 0 auto}
    .esu-multi-empty{padding:13px;color:#94a3b8;font-size:11px}
    .esu-status-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:12px}
    .esu-status{border:1px solid #e2e8f0;border-radius:14px;padding:14px}
    .esu-status .esu-switch-row{padding:0}
    .esu-error{border:1px solid #fecaca;background:#fef2f2;color:#991b1b;border-radius:14px;padding:15px 18px;margin-bottom:20px;font-size:12px}
    .esu-error ul{margin:8px 0 0;padding-left:18px}
    .esu-preview-image{display:none;width:100%;max-width:320px;aspect-ratio:8/5;object-fit:cover;border-radius:13px;border:1px solid #e2e8f0;margin-top:12px}
    .esu-actions{position:sticky;bottom:12px;z-index:30;display:flex;align-items:center;justify-content:space-between;gap:14px;padding:15px 18px;background:rgba(255,255,255,.96);backdrop-filter:blur(12px);border:1px solid #e2e8f0;border-radius:16px;box-shadow:0 12px 35px rgba(15,23,42,.12)}
    .esu-actions-note{font-size:11px;color:#64748b}
    .esu-btn{display:inline-flex;align-items:center;justify-content:center;border-radius:11px;padding:10px 17px;font-size:12px;font-weight:800;text-decoration:none;border:0;cursor:pointer}
    .esu-btn-secondary{background:#fff;border:1px solid #dbe3ee;color:#475569}
    .esu-btn-primary{background:#2563eb;color:#fff}
    @media(max-width:991px){.esu-col-8,.esu-col-6,.esu-col-4,.esu-col-3{grid-column:span 12}.esu-status-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}
    @media(max-width:575px){.esu-module-head,.esu-module-body{padding:18px}.esu-status-grid{grid-template-columns:1fr}.esu-actions{align-items:stretch;flex-direction:column}.esu-actions>div:last-child{display:flex;width:100%;gap:8px}.esu-actions .esu-btn{flex:1}}
</style>

<div class="esu-module-form">

@if($errors->any())
    <div class="esu-error">
        <strong>Please check the highlighted fields.</strong>
        <ul>
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<section class="esu-module-card">
    <div class="esu-module-head">
        <div>
            <h3>Module Information</h3>
            <p>Define the Module identity, Marketplace information and installable package.</p>
        </div>
    </div>

    <div class="esu-module-body">
        <div class="esu-grid">
            <div class="esu-field esu-col-6">
                <label>Module Name</label>
                <input type="text" name="name" value="{{ old('name', $module->name ?? '') }}" required>
            </div>

            <div class="esu-field esu-col-6">
                <label>Slug</label>
                <input type="text" name="slug" value="{{ old('slug', $module->slug ?? '') }}" required>
            </div>

            <div class="esu-field esu-col-4">
                <label>Version</label>
                <input type="text" name="version" value="{{ old('version', $module->version ?? '1.0.0') }}" required>
            </div>

            <div class="esu-field esu-col-4">
                <label>Module Type</label>
                <select name="type" required>
                    @foreach(['official','developer','private','core'] as $type)
                        <option value="{{ $type }}" @selected(old('type', $module->type ?? 'official') === $type)>
                            {{ ucfirst($type) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="esu-field esu-col-4">
                <label>Sidebar Menu Name</label>
                <input type="text" name="sidebar_menu_name" value="{{ old('sidebar_menu_name', $module->sidebar_menu_name ?? '') }}" placeholder="e.g. Store Management" required>
            </div>

            <div class="esu-field esu-col-12">
                <label>Description</label>
                <textarea name="description">{{ old('description', $module->description ?? '') }}</textarea>
            </div>

            <div class="esu-col-12">
                @include('admin.components.product-source-toggle', [
                    'product' => 'Module',
                    'mode' => old('package_source_mode', !empty($module->package_name ?? null) ? 'folder' : 'upload'),
                    'folderName' => 'package_name',
                    'folderValue' => $module->package_name ?? '',
                    'folderPlaceholder' => 'Search/select Module package folder',
                    'uploadName' => 'package_file',
                    'help' => 'Search an existing Esubiz Module package or switch right to upload a new ZIP release.',
                ])
            </div>

            <div class="esu-field esu-col-6">
                <label>Preview Photo</label>
                <input type="file" name="preview_image" id="module_preview_image" accept="image/png,image/jpeg,image/webp">
                <div class="esu-help">Recommended Marketplace preview: 1600 × 1000 px (8:5 landscape).</div>
                <img id="module_preview" class="esu-preview-image" src="" alt="Module preview">
            </div>
        </div>
    </div>
</section>

<section class="esu-module-card">
    <div class="esu-module-head">
        <div>
            <h3>Deployment & Pricing</h3>
            <p>Control where this Module is available and how SaaS access is billed.</p>
        </div>
        <div style="font-size:11px;font-weight:800;color:#2563eb;background:#eff6ff;border:1px solid #dbeafe;border-radius:999px;padding:7px 11px">
            Central Currency: {{ strtoupper($primaryCurrency) }}
        </div>
    </div>

    <div class="esu-module-body">
        <div class="esu-grid">

            <div class="esu-col-6">
                <div class="esu-deployment">
                    <div class="esu-deployment-title">
                        <strong>SaaS Deployment</strong>
                        <label class="esu-toggle">
                            <input type="hidden" name="saas_available" value="0">
                            <input type="checkbox" name="saas_available" value="1" @checked((bool)$saasAvailable)>
                            <span class="esu-slider"></span>
                        </label>
                    </div>

                    <div class="esu-grid">
                        <div class="esu-field esu-col-12">
                            <label>SaaS Price</label>
                            <div class="esu-currency">
                                <span class="esu-currency-code">{{ strtoupper($primaryCurrency) }}</span>
                                <input type="number" step="0.01" min="0" name="saas_price" value="{{ old('saas_price', $saasPrice ?? '') }}">
                            </div>
                        </div>

                        <div class="esu-field esu-col-6">
                            <label>Billing Interval</label>
                            <select name="saas_billing_interval" id="saas_billing_interval">
                                @foreach([1,3,6,12,24,36] as $interval)
                                    <option value="{{ $interval }}" @selected($selectedBillingInterval === $interval)>
                                        {{ $interval }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="esu-field esu-col-6">
                            <label>Billing Period</label>
                            <select name="saas_billing_period" id="saas_billing_period">
                                <option value="daily" @selected($selectedBillingPeriod === 'daily')>Day</option>
                                <option value="weekly" @selected($selectedBillingPeriod === 'weekly')>Week</option>
                                <option value="monthly" @selected($selectedBillingPeriod === 'monthly')>Month</option>
                                <option value="yearly" @selected($selectedBillingPeriod === 'yearly')>Year</option>
                            </select>
                        </div>
                    </div>

                    <div class="esu-billing-preview" id="module_billing_preview">
                        Billing: <strong>{{ $selectedBillingInterval }} {{ $billingUnit }}{{ $selectedBillingInterval === 1 ? '' : 's' }}</strong>
                    </div>

                    <div class="esu-switch-row">
                        <div class="esu-switch-copy">
                            <strong>Visible in SaaS Wizard</strong>
                            <span>Allow this Module to appear during SaaS website setup.</span>
                        </div>
                        <label class="esu-toggle">
                            <input type="hidden" name="saas_wizard_visible" value="0">
                            <input type="checkbox" name="saas_wizard_visible" value="1" @checked((bool)old('saas_wizard_visible', $module->saas_wizard_visible ?? true))>
                            <span class="esu-slider"></span>
                        </label>
                    </div>
                </div>
            </div>

            <div class="esu-col-6">
                <div class="esu-deployment">
                    <div class="esu-deployment-title">
                        <strong>Off-server Deployment</strong>
                        <label class="esu-toggle">
                            <input type="hidden" name="off_server_available" value="0">
                            <input type="checkbox" name="off_server_available" value="1" @checked((bool)$offServerAvailable)>
                            <span class="esu-slider"></span>
                        </label>
                    </div>

                    <div class="esu-field">
                        <label>Off-server Price</label>
                        <div class="esu-currency">
                            <span class="esu-currency-code">{{ strtoupper($primaryCurrency) }}</span>
                            <input type="number" step="0.01" min="0" name="off_server_price" value="{{ old('off_server_price', $offServerPrice ?? '') }}">
                        </div>
                        <div class="esu-help">Off-server Modules use one-time pricing.</div>
                    </div>

                    <div class="esu-switch-row" style="margin-top:18px">
                        <div class="esu-switch-copy">
                            <strong>Visible in Off-server Wizard</strong>
                            <span>Allow this Module to appear during off-server setup.</span>
                        </div>
                        <label class="esu-toggle">
                            <input type="hidden" name="off_server_wizard_visible" value="0">
                            <input type="checkbox" name="off_server_wizard_visible" value="1" @checked((bool)old('off_server_wizard_visible', $module->off_server_wizard_visible ?? true))>
                            <span class="esu-slider"></span>
                        </label>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>

<section class="esu-module-card">
    <div class="esu-module-head">
        <div>
            <h3>Compatibility</h3>
            <p>Choose the Website Types and Themes that can use this Module.</p>
        </div>
    </div>

    <div class="esu-module-body">
        <div class="esu-grid">

            <div class="esu-field esu-col-6">
                <label>Website Types</label>

                <div class="esu-multi" data-esu-multi>
                    <button type="button" class="esu-multi-trigger" data-esu-trigger>
                        <span
                            class="esu-multi-value {{ count($selectedWebsiteTypes) ? 'has-selection' : '' }}"
                            data-esu-value
                            data-placeholder="Select Website Types"
                        >
                            <span data-esu-summary>
                                {{ count($selectedWebsiteTypes)
                                    ? count($selectedWebsiteTypes) . ' selected'
                                    : 'Select Website Types' }}
                            </span>

                            <span class="esu-multi-count" data-esu-count>
                                {{ count($selectedWebsiteTypes) }}
                            </span>
                        </span>

                        <span class="esu-multi-arrow"></span>
                    </button>

                    <div class="esu-multi-menu">
                        <div class="esu-select-search">
                            <input
                                type="text"
                                data-esu-search
                                placeholder="Search Website Types..."
                                autocomplete="off"
                            >
                        </div>

                        <div class="esu-options">
                            @forelse($websiteTypes as $websiteType)
                                <label
                                    class="esu-option"
                                    data-search="{{ strtolower($websiteType->name) }}"
                                >
                                    <input
                                        type="checkbox"
                                        name="compatible_website_types[]"
                                        value="{{ $websiteType->id }}"
                                        @checked(in_array(
                                            (string)$websiteType->id,
                                            $selectedWebsiteTypes,
                                            true
                                        ))
                                    >
                                    <span>{{ $websiteType->name }}</span>
                                </label>
                            @empty
                                <div class="esu-multi-empty">
                                    No active Website Types found.
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            <div class="esu-field esu-col-6">
                <label>Themes</label>

                <div class="esu-multi" data-esu-multi>
                    <button type="button" class="esu-multi-trigger" data-esu-trigger>
                        <span
                            class="esu-multi-value {{ count($selectedThemes) ? 'has-selection' : '' }}"
                            data-esu-value
                            data-placeholder="Select Themes"
                        >
                            <span data-esu-summary>
                                {{ count($selectedThemes)
                                    ? count($selectedThemes) . ' selected'
                                    : 'Select Themes' }}
                            </span>

                            <span class="esu-multi-count" data-esu-count>
                                {{ count($selectedThemes) }}
                            </span>
                        </span>

                        <span class="esu-multi-arrow"></span>
                    </button>

                    <div class="esu-multi-menu">
                        <div class="esu-select-search">
                            <input
                                type="text"
                                data-esu-search
                                placeholder="Search Themes..."
                                autocomplete="off"
                            >
                        </div>

                        <div class="esu-options">
                            @forelse($themes as $theme)
                                <label
                                    class="esu-option"
                                    data-search="{{ strtolower($theme->name . ' ' . $theme->slug) }}"
                                >
                                    <input
                                        type="checkbox"
                                        name="compatible_themes[]"
                                        value="{{ $theme->slug }}"
                                        @checked(in_array(
                                            (string)$theme->slug,
                                            $selectedThemes,
                                            true
                                        ))
                                    >
                                    <span>{{ $theme->name }}</span>
                                </label>
                            @empty
                                <div class="esu-multi-empty">
                                    No Marketplace-ready Themes are currently available.
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            <div class="esu-field esu-col-12">
                <label>Product Categories</label>

                <div class="esu-multi" data-esu-multi>
                    <button type="button" class="esu-multi-trigger" data-esu-trigger>
                        <span
                            class="esu-multi-value {{ count($selectedMarketplaceCategories) ? 'has-selection' : '' }}"
                            data-esu-value
                            data-placeholder="Select Product Categories"
                        >
                            <span data-esu-summary>
                                {{ count($selectedMarketplaceCategories)
                                    ? count($selectedMarketplaceCategories) . ' selected'
                                    : 'Select Product Categories' }}
                            </span>

                            <span class="esu-multi-count" data-esu-count>
                                {{ count($selectedMarketplaceCategories) }}
                            </span>
                        </span>

                        <span class="esu-multi-arrow"></span>
                    </button>

                    <div class="esu-multi-menu">
                        <div class="esu-select-search">
                            <input
                                type="text"
                                data-esu-search
                                placeholder="Search Product Categories..."
                                autocomplete="off"
                            >
                        </div>

                        <div class="esu-options">
                            @forelse($marketplaceCategories as $category)
                                <label
                                    class="esu-option"
                                    data-search="{{ strtolower($category->name . ' ' . ($category->slug ?? '')) }}"
                                >
                                    <input
                                        type="checkbox"
                                        name="marketplace_category_ids[]"
                                        value="{{ $category->id }}"
                                        @checked(in_array(
                                            (string)$category->id,
                                            $selectedMarketplaceCategories,
                                            true
                                        ))
                                    >
                                    <span>{{ $category->name }}</span>
                                </label>
                            @empty
                                <div class="esu-multi-empty">
                                    No active Module product categories found.
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>

                <div class="esu-help">
                    Select the Admin-managed Module categories used to organize this product in Marketplace.
                </div>
            </div>

        </div>
    </div>
</section>

<section class="esu-module-card">
    <div class="esu-module-head">
        <div>
            <h3>Marketplace Status</h3>
            <p>Control verification, visibility, ranking and availability.</p>
        </div>
    </div>

    <div class="esu-module-body">
        <div class="esu-status-grid">
            @foreach([
                ['is_verified','Verified','Module has been reviewed.', $module->is_verified ?? true],
                ['is_featured','Featured','Highlight in Marketplace.', $catalog?->is_featured ?? false],
                ['is_public','Public','Visible to eligible customers.', $catalog?->is_public ?? true],
                ['is_active','Active','Module can be used and purchased.', $module->is_active ?? true],
            ] as [$name,$title,$help,$default])
                <div class="esu-status">
                    <div class="esu-switch-row">
                        <div class="esu-switch-copy">
                            <strong>{{ $title }}</strong>
                            <span>{{ $help }}</span>
                        </div>
                        <label class="esu-toggle">
                            <input type="hidden" name="{{ $name }}" value="0">
                            <input type="checkbox" name="{{ $name }}" value="1" @checked((bool)old($name, $default))>
                            <span class="esu-slider"></span>
                        </label>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- Preserve existing Core function configuration contract --}}
@foreach((array)$coreConfig as $key => $value)
    @if(is_scalar($value))
        <input type="hidden" name="core_function_configuration[{{ $key }}]" value="{{ $value }}">
    @endif
@endforeach

<div class="esu-actions">
    <div class="esu-actions-note">
        {{ $editing ? 'Update the Module configuration and Marketplace settings.' : 'Create the Module and publish its Marketplace configuration.' }}
    </div>
    <div>
        <a href="{{ route('admin.modules.index') }}" class="esu-btn esu-btn-secondary">Cancel</a>
        <button type="submit" class="esu-btn esu-btn-primary">
            {{ $editing ? 'Save Module' : 'Create Module' }}
        </button>
    </div>
</div>

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-esu-multi]').forEach(function (multi) {
        const trigger = multi.querySelector('[data-esu-trigger]');
        const search = multi.querySelector('[data-esu-search]');
        const value = multi.querySelector('[data-esu-value]');
        const summary = multi.querySelector('[data-esu-summary]');
        const countBadge = multi.querySelector('[data-esu-count]');
        const checks = Array.from(
            multi.querySelectorAll('.esu-option input[type="checkbox"]')
        );

        function refreshSummary() {
            const checked = checks.filter(function (checkbox) {
                return checkbox.checked;
            });

            const count = checked.length;
            const placeholder = value.dataset.placeholder || 'Select';

            summary.textContent = count
                ? count + ' selected'
                : placeholder;

            countBadge.textContent = count;
            value.classList.toggle('has-selection', count > 0);
        }

        trigger?.addEventListener('click', function (event) {
            event.stopPropagation();

            document.querySelectorAll('[data-esu-multi].open').forEach(function (other) {
                if (other !== multi) {
                    other.classList.remove('open');
                }
            });

            multi.classList.toggle('open');

            if (multi.classList.contains('open')) {
                setTimeout(function () {
                    search?.focus();
                }, 0);
            }
        });

        search?.addEventListener('click', function (event) {
            event.stopPropagation();
        });

        search?.addEventListener('input', function () {
            const query = search.value.toLowerCase().trim();

            multi.querySelectorAll('.esu-option').forEach(function (option) {
                option.style.display =
                    (option.dataset.search || '').includes(query)
                        ? 'flex'
                        : 'none';
            });
        });

        checks.forEach(function (checkbox) {
            checkbox.addEventListener('change', refreshSummary);
        });

        multi.querySelector('.esu-multi-menu')?.addEventListener(
            'click',
            function (event) {
                event.stopPropagation();
            }
        );

        refreshSummary();
    });

    document.addEventListener('click', function () {
        document.querySelectorAll('[data-esu-multi].open').forEach(function (multi) {
            multi.classList.remove('open');
        });
    });

    const interval = document.getElementById('saas_billing_interval');
    const period = document.getElementById('saas_billing_period');
    const preview = document.getElementById('module_billing_preview');

    function updateBillingPreview() {
        if (!interval || !period || !preview) return;

        const count = parseInt(interval.value || '1', 10);
        const units = {
            daily: 'Day',
            weekly: 'Week',
            monthly: 'Month',
            yearly: 'Year'
        };

        const unit = units[period.value] || 'Month';

        preview.innerHTML =
            'Billing: <strong>' +
            count + ' ' + unit + (count === 1 ? '' : 's') +
            '</strong>';
    }

    interval?.addEventListener('change', updateBillingPreview);
    period?.addEventListener('change', updateBillingPreview);
    updateBillingPreview();

    const imageInput = document.getElementById('module_preview_image');
    const imagePreview = document.getElementById('module_preview');

    imageInput?.addEventListener('change', function () {
        const file = this.files && this.files[0];
        if (!file || !imagePreview) return;

        imagePreview.src = URL.createObjectURL(file);
        imagePreview.style.display = 'block';
    });
});
</script>
