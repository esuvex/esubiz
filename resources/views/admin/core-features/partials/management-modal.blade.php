{{-- ESUBIZ_CORE_FEATURE_MANAGEMENT_MODAL_V1 --}}
<div id="feature-{{ $feature->id }}"
     class="fixed inset-0 z-[9999] hidden overflow-y-auto overscroll-contain bg-slate-950/50 p-3 sm:p-6"
     data-feature-modal>

    <div class="mx-auto flex min-h-full w-full max-w-6xl items-start justify-center sm:items-center">

        <div class="my-auto flex h-[calc(100dvh-1.5rem)] w-full flex-col overflow-hidden rounded-2xl bg-slate-50 shadow-2xl sm:h-[90dvh] sm:max-h-[900px] sm:rounded-3xl">

            <div class="shrink-0 flex items-center justify-between border-b border-slate-200 bg-white px-5 py-4 sm:px-6">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-[0.14em] text-blue-600">
                    Core Feature
                </p>
                <h3 class="mt-1 text-xl font-bold text-slate-950">
                    {{ $feature->name }}
                </h3>
            </div>

            <button type="button"
                    onclick="closeFeatureModal('feature-{{ $feature->id }}')"
                    class="flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200 bg-white text-xl text-slate-500 hover:bg-slate-50">
                ×
            </button>
        </div>

            <div class="min-h-0 flex-1 overflow-y-auto overscroll-contain p-4 sm:p-6"
                 data-feature-modal-scroll>

                        <div class="mb-6 flex items-center justify-between">
                            <div>
                                <h4 class="font-semibold text-slate-950">
                                    Edit {{ $feature->name }}
                                </h4>
                                <p class="mt-1 text-sm text-slate-500">
                                    Update feature details and configure its limits.
                                </p>
                            </div>

                            <form method="POST"
                                action="{{ route('admin.core-features.toggle', $feature->id) }}">
                                @csrf
                                <button class="esubiz-admin-secondary text-xs">
                                    {{ $feature->is_active ? 'Disable Feature' : 'Enable Feature' }}
                                </button>
                            </form>
                        </div>

                        {{-- FEATURE DETAILS --}}
                        <form method="POST"
                            action="{{ route('admin.core-features.update', $feature->id) }}"
                            class="grid grid-cols-1 gap-4 rounded-2xl border border-slate-200 bg-white p-5 md:grid-cols-3">
                            @csrf

                            <div>
                                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-400">
                                    Feature Name
                                </label>
                                <input name="name"
                                    value="{{ $feature->name }}"
                                    required
                                    class="esubiz-admin-input">
                            </div>

                            <div>
                                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-400">
                                    Category
                                </label>
                                <input name="category"
                                    value="{{ $feature->category }}"
                                    required
                                    class="esubiz-admin-input">
                            </div>

                            <div>
                                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-400">
                                    Type
                                </label>
                                <select name="type" class="esubiz-admin-input">
                                    @foreach(['feature','service','communication','resource','integration'] as $type)
                                        <option value="{{ $type }}" @selected($feature->type === $type)>
                                            {{ ucfirst($type) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="flex justify-end md:col-span-3">
                                <button class="esubiz-admin-primary">
                                    Save Feature Changes
                                </button>
                            </div>
                        </form>

                        {{-- LIMITS --}}
                        @php
                            $featureLimits = $limits->get($feature->id, collect());
                        @endphp

                        <div class="my-7 flex items-center gap-3">
                            <div class="h-px flex-1 bg-slate-200"></div>
                            <span class="text-[11px] font-bold uppercase tracking-[0.16em] text-slate-400">
                                SaaS Core Limits
                            </span>
                            <div class="h-px flex-1 bg-slate-200"></div>
                        </div>

                        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white"
                             data-limit-table
                             data-feature-id="{{ $feature->id }}"
                             data-ajax-url="{{ route('admin.core-features.limits.index', $feature->id) }}"
                             data-page-size="10">

                            <div class="flex flex-col gap-3 border-b border-slate-200 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                                <div>
                                    <h5 class="text-sm font-semibold text-slate-900">
                                        Core Limits
                                    </h5>
                                    <p class="mt-1 text-xs text-slate-500">
                                        Live SaaS limits. Off-server Core remains unlimited.
                                    </p>
                                </div>

                                <button type="button"
                                    onclick="openLimitModal('limit-add-{{ $feature->id }}')"
                                    class="esubiz-admin-primary">
                                    + Add Limit
                                </button>
                            </div>

                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-slate-200">
                                    <thead class="bg-slate-50">
                                        <tr>
                                            @foreach(['Limit','Key','Type','Default SaaS Limit','Unit','Usage Source','Status',''] as $heading)
                                                <th class="whitespace-nowrap px-4 py-3 text-left text-[10px] font-bold uppercase tracking-[0.12em] text-slate-400">
                                                    {{ $heading }}
                                                </th>
                                            @endforeach
                                        </tr>
                                    </thead>

                                    <tbody class="divide-y divide-slate-100 bg-white">
                                        @forelse($featureLimits as $limit)
                                            @php
                                                $usage = is_array($limit->resource_usage ?? null)
                                                    ? $limit->resource_usage
                                                    : [];

                                                $usageDriver = $usage['driver'] ?? null;
                                                $usageTable = $usage['table'] ?? null;
                                                $isUnlimited = (bool) $limit->is_unlimited
                                                    || $limit->value_type === 'unlimited';
                                            @endphp

                                            <tr data-limit-row>
                                                <td class="whitespace-nowrap px-4 py-4">
                                                    <div class="text-sm font-semibold text-slate-900">
                                                        {{ $limit->name }}
                                                    </div>
                                                </td>

                                                <td class="whitespace-nowrap px-4 py-4">
                                                    <code class="rounded-lg bg-slate-100 px-2 py-1 text-xs text-slate-600">
                                                        {{ $limit->limit_key }}
                                                    </code>
                                                </td>

                                                <td class="whitespace-nowrap px-4 py-4 text-sm text-slate-600">
                                                    {{ ucfirst($limit->value_type) }}
                                                </td>

                                                <td class="whitespace-nowrap px-4 py-4 text-sm font-semibold text-slate-900">
                                                    {{ $isUnlimited ? 'Unlimited' : number_format((int) ($limit->default_value ?? 0)) }}
                                                </td>

                                                <td class="whitespace-nowrap px-4 py-4 text-sm text-slate-600">
                                                    {{ $limit->unit ?: '—' }}
                                                </td>

                                                <td class="whitespace-nowrap px-4 py-4 text-sm text-slate-600">
                                                    @if($usageDriver === 'database_count')
                                                        <span class="font-medium">Database Count</span>
                                                        @if($usageTable)
                                                            <span class="block text-xs text-slate-400">
                                                                {{ $usageTable }}
                                                            </span>
                                                        @endif
                                                    @else
                                                        <span class="text-slate-400">Not configured</span>
                                                    @endif
                                                </td>

                                                <td class="whitespace-nowrap px-4 py-4">
                                                    @if($limit->is_active)
                                                        <span class="inline-flex rounded-full bg-blue-50 px-2.5 py-1 text-xs font-semibold text-blue-700">
                                                            Active
                                                        </span>
                                                    @else
                                                        <span class="inline-flex rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-500">
                                                            Inactive
                                                        </span>
                                                    @endif
                                                </td>

                                                <td class="relative whitespace-nowrap px-4 py-4 text-right">
                                                    <button type="button"
                                                        onclick="toggleLimitMenu(event, 'limit-menu-{{ $limit->id }}')"
                                                        class="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-slate-200 bg-white text-lg font-bold text-slate-500 hover:bg-slate-50">
                                                        ⋮
                                                    </button>

                                                    <div id="limit-menu-{{ $limit->id }}"
                                                        class="absolute right-4 z-30 mt-2 hidden w-44 overflow-hidden rounded-xl border border-slate-200 bg-white py-1 text-left shadow-xl">

                                                        <button type="button"
                                                            onclick="openLimitModal('limit-view-{{ $limit->id }}')"
                                                            class="block w-full px-4 py-2.5 text-left text-sm font-medium text-slate-700 hover:bg-slate-50">
                                                            View
                                                        </button>

                                                        <button type="button"
                                                            onclick="openLimitModal('limit-edit-{{ $limit->id }}')"
                                                            class="block w-full px-4 py-2.5 text-left text-sm font-medium text-slate-700 hover:bg-slate-50">
                                                            Edit
                                                        </button>

                                                        <form method="POST"
                                                            action="{{ route('admin.core-features.limits.toggle', $limit->id) }}">
                                                            @csrf
                                                            <button type="submit"
                                                                class="block w-full px-4 py-2.5 text-left text-sm font-medium text-slate-700 hover:bg-slate-50">
                                                                {{ $limit->is_active ? 'Disable' : 'Enable' }}
                                                            </button>
                                                        </form>
                                                        <form method="POST"
                                                            action="{{ route('admin.core-features.limits.destroy', $limit->id) }}"
                                                            onsubmit="return confirm('Soft delete this Core limit?');">
                                                            @csrf
                                                            <button type="submit"
                                                                class="block w-full px-4 py-2.5 text-left text-sm font-semibold text-red-600 hover:bg-red-50">
                                                                Delete
                                                            </button>
                                                        </form>

                                                    </div>
                                                </td>
                                            </tr>

                                            {{-- VIEW LIMIT --}}
                                            <div id="limit-view-{{ $limit->id }}"
                                                class="fixed inset-0 z-[80] hidden items-center justify-center bg-slate-950/50 p-4"
                                                data-limit-modal>
                                                <div class="w-full max-w-xl rounded-3xl bg-white p-6 shadow-2xl">
                                                    <div class="flex items-start justify-between gap-4">
                                                        <div>
                                                            <p class="text-[11px] font-bold uppercase tracking-[0.14em] text-blue-600">
                                                                Core Limit
                                                            </p>
                                                            <h3 class="mt-1 text-xl font-bold text-slate-900">
                                                                {{ $limit->name }}
                                                            </h3>
                                                        </div>
                                                        <button type="button"
                                                            onclick="closeLimitModal('limit-view-{{ $limit->id }}')"
                                                            class="text-2xl text-slate-400">×</button>
                                                    </div>

                                                    <dl class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-2">
                                                        <div>
                                                            <dt class="text-xs font-semibold uppercase text-slate-400">Key</dt>
                                                            <dd class="mt-1 text-sm font-medium text-slate-900">{{ $limit->limit_key }}</dd>
                                                        </div>
                                                        <div>
                                                            <dt class="text-xs font-semibold uppercase text-slate-400">Type</dt>
                                                            <dd class="mt-1 text-sm font-medium text-slate-900">{{ ucfirst($limit->value_type) }}</dd>
                                                        </div>
                                                        <div>
                                                            <dt class="text-xs font-semibold uppercase text-slate-400">SaaS Limit</dt>
                                                            <dd class="mt-1 text-sm font-medium text-slate-900">
                                                                {{ $isUnlimited ? 'Unlimited' : number_format((int) ($limit->default_value ?? 0)) }}
                                                            </dd>
                                                        </div>
                                                        <div>
                                                            <dt class="text-xs font-semibold uppercase text-slate-400">Unit</dt>
                                                            <dd class="mt-1 text-sm font-medium text-slate-900">{{ $limit->unit ?: '—' }}</dd>
                                                        </div>
                                                        <div class="sm:col-span-2">
                                                            <dt class="text-xs font-semibold uppercase text-slate-400">Usage Source</dt>
                                                            <dd class="mt-1 text-sm font-medium text-slate-900">
                                                                {{ $usageDriver === 'database_count' ? 'Database Count — '.($usageTable ?: 'Not configured') : 'Not configured' }}
                                                            </dd>
                                                        </div>
                                                    </dl>
                                                </div>
                                            </div>

                                            {{-- EDIT LIMIT --}}
                                            <div id="limit-edit-{{ $limit->id }}"
                                                class="fixed inset-0 z-[80] hidden items-center justify-center bg-slate-950/50 p-4"
                                                data-limit-modal>
                                                <div class="w-full max-w-2xl rounded-3xl bg-white p-6 shadow-2xl">
                                                    <div class="flex items-start justify-between gap-4">
                                                        <div>
                                                            <p class="text-[11px] font-bold uppercase tracking-[0.14em] text-blue-600">
                                                                Edit Core Limit
                                                            </p>
                                                            <h3 class="mt-1 text-xl font-bold text-slate-900">{{ $limit->name }}</h3>
                                                        </div>
                                                        <button type="button"
                                                            onclick="closeLimitModal('limit-edit-{{ $limit->id }}')"
                                                            class="text-2xl text-slate-400">×</button>
                                                    </div>

                                                    <form method="POST"
                                                        action="{{ route('admin.core-features.limits.update', $limit->id) }}"
                                                        class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-2">
                                                        @csrf

                                                        <div>
                                                            <label class="mb-1 block text-xs font-semibold text-slate-600">Limit Name</label>
                                                            <input name="name" value="{{ $limit->name }}" required class="esubiz-admin-input">
                                                        </div>

                                                        <div>
                                                            <label class="mb-1 block text-xs font-semibold text-slate-600">Type</label>
                                                            <select name="value_type" class="esubiz-admin-input">
                                                                @foreach(['quantity','boolean','storage','bandwidth','credits','unlimited'] as $type)
                                                                    <option value="{{ $type }}" @selected($limit->value_type === $type)>
                                                                        {{ ucfirst($type) }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        </div>

                                                        <div>
                                                            <label class="mb-1 block text-xs font-semibold text-slate-600">Default SaaS Limit</label>
                                                            <input name="default_value" type="number" min="0"
                                                                value="{{ $limit->default_value }}" class="esubiz-admin-input">
                                                        </div>

                                                        <div>
                                                            <label class="mb-1 block text-xs font-semibold text-slate-600">Unit</label>
                                                            <input name="unit" value="{{ $limit->unit }}" placeholder="records" class="esubiz-admin-input">
                                                        </div>

                                                        <div>
                                                            <label class="mb-1 block text-xs font-semibold text-slate-600">Usage Source</label>
                                                            <select name="usage_driver"
                                                                onchange="toggleUsageTable(this)"
                                                                class="esubiz-admin-input">
                                                                <option value="">None</option>
                                                                <option value="database_count" @selected($usageDriver === 'database_count')>
                                                                    Database Count
                                                                </option>
                                                            </select>
                                                        </div>

                                                        <div data-usage-table-field>
                                                            <label class="mb-1 block text-xs font-semibold text-slate-600">Tenant Table</label>
                                                            <div data-tenant-table-picker
                                                                data-current-table="{{ $usageTable }}">
                                                                <select name="usage_table"
                                                                    class="esubiz-admin-input"
                                                                    data-tenant-table-select>
                                                                    @if($usageTable)
                                                                        <option value="{{ $usageTable }}" selected>{{ $usageTable }}</option>
                                                                    @else
                                                                        <option value="">Select tenant table</option>
                                                                    @endif
                                                                </select>
                                                                <p class="mt-1 text-xs text-slate-400" data-tenant-table-status>
                                                                    Tables load automatically from the current Core schema.
                                                                </p>
                                                            </div>
                                                            <input type="hidden" name="usage_connection" value="tenant">
                                                        </div>

                                                        <div class="flex justify-end gap-3 sm:col-span-2">
                                                            <button type="button"
                                                                onclick="closeLimitModal('limit-edit-{{ $limit->id }}')"
                                                                class="esubiz-admin-secondary">
                                                                Cancel
                                                            </button>
                                                            <button type="submit" class="esubiz-admin-primary">
                                                                Save Changes
                                                            </button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>

                                        @empty
                                            <tr data-limit-empty>
                                                <td colspan="8" class="px-5 py-12 text-center text-sm text-slate-500">
                                                    No limits configured for this feature.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            <div data-limit-ajax-modals></div>

                            <div class="flex items-center justify-between border-t border-slate-200 px-5 py-4">
                                <span class="text-xs text-slate-500" data-limit-page-info></span>

                                <div class="flex gap-2">
                                    <button type="button"
                                        data-limit-prev
                                        class="esubiz-admin-secondary">
                                        Previous
                                    </button>
                                    <button type="button"
                                        data-limit-next
                                        class="esubiz-admin-secondary">
                                        Next
                                    </button>
                                </div>
                            </div>
                        </div>

                        {{-- ADD LIMIT --}}
                        <div id="limit-add-{{ $feature->id }}"
                            class="fixed inset-0 z-[80] hidden items-center justify-center bg-slate-950/50 p-4"
                            data-limit-modal>
                            <div class="w-full max-w-2xl rounded-3xl bg-white p-6 shadow-2xl">
                                <div class="flex items-start justify-between gap-4">
                                    <div>
                                        <p class="text-[11px] font-bold uppercase tracking-[0.14em] text-blue-600">
                                            Add Core Limit
                                        </p>
                                        <h3 class="mt-1 text-xl font-bold text-slate-900">
                                            {{ $feature->name }}
                                        </h3>
                                    </div>
                                    <button type="button"
                                        onclick="closeLimitModal('limit-add-{{ $feature->id }}')"
                                        class="text-2xl text-slate-400">×</button>
                                </div>

                                <form method="POST"
                                    action="{{ route('admin.core-features.limits.store', $feature->id) }}"
                                    class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-2">
                                    @csrf

                                    <div>
                                        <label class="mb-1 block text-xs font-semibold text-slate-600">Limit Key</label>
                                        <input name="limit_key" required placeholder="pages" class="esubiz-admin-input">
                                    </div>

                                    <div>
                                        <label class="mb-1 block text-xs font-semibold text-slate-600">Limit Name</label>
                                        <input name="name" required placeholder="Pages" class="esubiz-admin-input">
                                    </div>

                                    <div>
                                        <label class="mb-1 block text-xs font-semibold text-slate-600">Type</label>
                                        <select name="value_type" class="esubiz-admin-input">
                                            <option value="quantity">Quantity</option>
                                            <option value="boolean">Boolean</option>
                                            <option value="storage">Storage</option>
                                            <option value="bandwidth">Bandwidth</option>
                                            <option value="credits">Credits</option>
                                            <option value="unlimited">Unlimited</option>
                                        </select>
                                    </div>

                                    <div>
                                        <label class="mb-1 block text-xs font-semibold text-slate-600">Default SaaS Limit</label>
                                        <input name="default_value" type="number" min="0" value="0" class="esubiz-admin-input">
                                    </div>

                                    <div>
                                        <label class="mb-1 block text-xs font-semibold text-slate-600">Unit</label>
                                        <input name="unit" placeholder="records" class="esubiz-admin-input">
                                    </div>

                                    <div>
                                        <label class="mb-1 block text-xs font-semibold text-slate-600">Usage Source</label>
                                        <select name="usage_driver"
                                            onchange="toggleUsageTable(this)"
                                            class="esubiz-admin-input">
                                            <option value="">None</option>
                                            <option value="database_count">Database Count</option>
                                        </select>
                                    </div>

                                    <div data-usage-table-field class="hidden sm:col-span-2">
                                        <label class="mb-1 block text-xs font-semibold text-slate-600">Tenant Table</label>
                                        <div data-tenant-table-picker data-current-table="">
                                            <select name="usage_table"
                                                class="esubiz-admin-input"
                                                data-tenant-table-select>
                                                <option value="">Select tenant table</option>
                                            </select>
                                            <p class="mt-1 text-xs text-slate-400" data-tenant-table-status>
                                                Tables load automatically from the current Core schema.
                                            </p>
                                        </div>
                                        <input type="hidden" name="usage_connection" value="tenant">
                                    </div>

                                    <div class="flex justify-end gap-3 sm:col-span-2">
                                        <button type="button"
                                            onclick="closeLimitModal('limit-add-{{ $feature->id }}')"
                                            class="esubiz-admin-secondary">
                                            Cancel
                                        </button>
                                        <button type="submit" class="esubiz-admin-primary">
                                            Add Limit
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>

            </div>
        </div>
    </div>
</div>
