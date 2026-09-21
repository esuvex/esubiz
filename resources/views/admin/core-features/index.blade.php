@extends('admin.layouts.app')

@section('content')

<x-admin.ui title="Features & Limits"
    description="Manage the capabilities available across Esubiz Core, including feature availability and usage limits.">

    <x-slot:actions>
        <button type="button"
            onclick="togglePanel('add-feature-panel')"
            class="esubiz-admin-primary">
            + Add Feature
        </button>
    </x-slot:actions>

    @if(session('success'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">
            {{ session('success') }}
        </div>
    @endif

    {{-- ADD FEATURE --}}
    <div id="add-feature-panel" class="hidden">
        <x-admin.card>
            <div class="mb-6 flex items-start justify-between gap-4">
                <div>
                    <div class="text-xs font-semibold uppercase tracking-[0.14em] text-blue-600">
                        Core Configuration
                    </div>
                    <h2 class="mt-2 text-xl font-semibold text-slate-950">
                        Add Core Feature
                    </h2>
                    <p class="mt-1 text-sm text-slate-500">
                        Register a new capability for the Esubiz Core platform.
                    </p>
                </div>

                <button type="button"
                    onclick="togglePanel('add-feature-panel')"
                    class="esubiz-admin-secondary">
                    Close
                </button>
            </div>

            <form method="POST"
                action="{{ route('admin.core-features.store') }}"
                class="grid grid-cols-1 gap-4 md:grid-cols-2">
                @csrf

                <input name="key" required
                    placeholder="Feature key · e.g. crm"
                    class="esubiz-admin-input">

                <input name="name" required
                    placeholder="Feature name"
                    class="esubiz-admin-input">

                <input name="category" required
                    placeholder="Category · e.g. CRM"
                    class="esubiz-admin-input">

                <select name="type" class="esubiz-admin-input">
                    <option value="feature">Feature</option>
                    <option value="service">Service</option>
                    <option value="communication">Communication</option>
                    <option value="resource">Resource</option>
                    <option value="integration">Integration</option>
                </select>

                <textarea name="description" rows="3"
                    placeholder="Short description"
                    class="esubiz-admin-input md:col-span-2"></textarea>

                <label class="flex items-center gap-3 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 md:col-span-2">
                    <input type="checkbox" name="is_active" value="1" checked
                        class="rounded border-slate-300 text-blue-600">
                    <span class="text-sm font-medium text-slate-700">
                        Make this feature active immediately
                    </span>
                </label>

                <div class="flex justify-end gap-3 md:col-span-2">
                    <button type="button"
                        onclick="togglePanel('add-feature-panel')"
                        class="esubiz-admin-secondary">
                        Cancel
                    </button>

                    <button class="esubiz-admin-primary">
                        Create Feature
                    </button>
                </div>
            </form>
        </x-admin.card>
    </div>

{{-- FEATURE REGISTRY --}}
<div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm"
     data-feature-table
     data-ajax-url="{{ route('admin.core-features.list') }}">

    <div class="flex flex-col gap-3 border-b border-slate-200 px-6 py-5 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-lg font-semibold text-slate-950">
                Core Feature Registry
            </h2>
            <p class="mt-1 text-sm text-slate-500">
                {{ $features->total() }} configured features
            </p>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Feature</th>
                    <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Category</th>
                    <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Type</th>
                    <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Limits</th>
                    <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Status</th>
                    <th class="px-6 py-3 text-right text-xs font-bold uppercase tracking-wider text-slate-500">Actions</th>
                </tr>
            </thead>

            <tbody class="divide-y divide-slate-100 bg-white" data-feature-rows>
                @forelse($features as $feature)
                    @php
                        $featureLimitCount = DB::table('core_feature_limits')
                            ->where('core_feature_id', $feature->id)
                            ->whereNull('deleted_at')
                            ->count();
                    @endphp

                    <tr class="hover:bg-slate-50/70">
                        <td class="px-6 py-4">
                            <div class="flex min-w-[220px] items-center gap-3">
                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-slate-100 text-sm font-bold text-slate-700">
                                    {{ strtoupper(substr($feature->name, 0, 1)) }}
                                </div>
                                <div class="min-w-0">
                                    <div class="font-semibold text-slate-950">{{ $feature->name }}</div>
                                    <div class="mt-0.5 text-xs text-slate-400">{{ $feature->key }}</div>
                                </div>
                            </div>
                        </td>

                        <td class="px-6 py-4 text-sm text-slate-600">
                            {{ $feature->category }}
                        </td>

                        <td class="px-6 py-4 text-sm text-slate-600">
                            {{ ucfirst($feature->type) }}
                        </td>

                        <td class="px-6 py-4 text-sm font-semibold text-slate-700">
                            {{ $featureLimitCount }}
                        </td>

                        <td class="px-6 py-4">
                            @if($feature->is_active)
                                <x-admin.badge type="success">Active</x-admin.badge>
                            @else
                                <x-admin.badge type="danger">Inactive</x-admin.badge>
                            @endif
                        </td>

                        <td class="px-6 py-4 text-right">
                            <div class="relative inline-block text-left" data-feature-actions>
                                <button type="button"
                                        onclick="toggleFeatureActions(this)"
                                        class="flex h-9 w-9 items-center justify-center rounded-xl border border-slate-200 bg-white text-lg font-bold text-slate-500 hover:bg-slate-50">
                                    ⋮
                                </button>

                                <div class="absolute right-0 z-30 mt-2 hidden w-44 overflow-hidden rounded-xl border border-slate-200 bg-white py-1 text-left shadow-xl"
                                     data-feature-action-menu>
                                    <button type="button"
                                            onclick="openFeatureModal('feature-{{ $feature->id }}')"
                                            class="block w-full px-4 py-2.5 text-left text-sm font-medium text-slate-700 hover:bg-slate-50">
                                        View
                                    </button>

                                    <button type="button"
                                            onclick="openFeatureModal('feature-{{ $feature->id }}')"
                                            class="block w-full px-4 py-2.5 text-left text-sm font-medium text-slate-700 hover:bg-slate-50">
                                        Edit
                                    </button>

                                    <form method="POST"
                                          action="{{ route('admin.core-features.toggle', $feature->id) }}">
                                        @csrf
                                        <button class="block w-full px-4 py-2.5 text-left text-sm font-medium text-slate-700 hover:bg-slate-50">
                                            {{ $feature->is_active ? 'Disable' : 'Enable' }}
                                        </button>
                                    </form>
                                        <form method="POST"
                                            action="{{ route('admin.core-features.destroy', $feature->id) }}"
                                            onsubmit="return confirm('Soft delete this Core feature and its limits?');">
                                            @csrf
                                            <button type="submit"
                                                class="block w-full px-4 py-2.5 text-left text-sm font-semibold text-red-600 hover:bg-red-50">
                                                Delete
                                            </button>
                                        </form>

                                </div>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-14 text-center text-sm text-slate-500">
                            No Core features configured.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="flex items-center justify-between border-t border-slate-200 px-6 py-4">
        <span class="text-xs text-slate-500" data-feature-page-info>
            @if($features->total())
                Showing {{ $features->firstItem() }}–{{ $features->lastItem() }} of {{ $features->total() }}
            @endif
        </span>

        <div class="flex gap-2">
            <button type="button"
                    data-feature-prev
                    class="esubiz-admin-secondary"
                    @disabled(!$features->previousPageUrl())>
                Previous
            </button>

            <button type="button"
                    data-feature-next
                    class="esubiz-admin-secondary"
                    @disabled(!$features->nextPageUrl())>
                Next
            </button>
        </div>
    </div>
</div>

<div data-feature-modals>
    @foreach($features as $feature)
        @include('admin.core-features.partials.management-modal', [
            'feature' => $feature,
        ])
    @endforeach
</div>

</x-admin.ui>

<script>
function openFeatureModal(id) {
    const modal = document.getElementById(id);
    if (!modal) return;

    document.querySelectorAll('[data-feature-modal]').forEach(item => {
        item.classList.add('hidden');
        item.classList.remove('flex');
    });

    modal.classList.remove('hidden');
    modal.classList.add('flex');
    document.body.classList.add('overflow-hidden');
}

function closeFeatureModal(id) {
    const modal = document.getElementById(id);
    if (!modal) return;

    modal.classList.add('hidden');
    modal.classList.remove('flex');
    document.body.classList.remove('overflow-hidden');
}

function toggleFeatureActions(button) {
    const wrapper = button.closest('[data-feature-actions]');
    const menu = wrapper?.querySelector('[data-feature-action-menu]');
    if (!menu) return;

    document.querySelectorAll('[data-feature-action-menu]').forEach(item => {
        if (item !== menu) item.classList.add('hidden');
    });

    menu.classList.toggle('hidden');
}

function escapeFeatureHtml(value) {
    return String(value ?? '')
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');
}

document.addEventListener('click', function (event) {
    if (!event.target.closest('[data-feature-actions]')) {
        document.querySelectorAll('[data-feature-action-menu]').forEach(item => {
            item.classList.add('hidden');
        });
    }

    const modal = event.target.closest('[data-feature-modal]');

    if (modal && event.target === modal) {
        closeFeatureModal(modal.id);
    }
});


document.addEventListener('click', async function (event) {
    const trigger = event.target.closest('[data-feature-load]');
    if (!trigger) return;

    event.preventDefault();

    const featureId = Number(trigger.dataset.featureLoad || 0);
    if (!featureId) return;

    const modalId = 'feature-' + featureId;
    const existing = document.getElementById(modalId);

    if (existing) {
        openFeatureModal(modalId);
        return;
    }

    const modals = document.querySelector('[data-feature-modals]');
    if (!modals) return;

    trigger.disabled = true;

    try {
        const response = await fetch(
            `{{ url('/admin/core/features') }}/${featureId}/management`,
            {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            }
        );

        if (!response.ok) {
            throw new Error('Feature management request failed.');
        }

        const payload = await response.json();

        if (!payload.html) {
            throw new Error('Feature management HTML was not returned.');
        }

        modals.insertAdjacentHTML('beforeend', payload.html);

        openFeatureModal(modalId);

        /*
         * Each loaded modal contains its own Core Limits table.
         * Initialise its existing AJAX limit paginator using the
         * page-level initializer already defined below.
         */
        const loadedModal = document.getElementById(modalId);

        if (
            loadedModal
            && typeof window.initializeCoreLimitTables === 'function'
        ) {
            window.initializeCoreLimitTables(loadedModal);
        }

        if (
            loadedModal
            && typeof window.initializeTenantTablePickers === 'function'
        ) {
            window.initializeTenantTablePickers(loadedModal);
        }
    } catch (error) {
        console.error(error);
    } finally {
        trigger.disabled = false;
    }
});

document.addEventListener('DOMContentLoaded', function () {
    const table = document.querySelector('[data-feature-table]');
    if (!table) return;

    const endpoint = table.dataset.ajaxUrl;
    const rows = table.querySelector('[data-feature-rows]');
    const info = table.querySelector('[data-feature-page-info]');
    const previous = table.querySelector('[data-feature-prev]');
    const next = table.querySelector('[data-feature-next]');
    const modals = document.querySelector('[data-feature-modals]');

    let currentPage = {{ (int) $features->currentPage() }};
    let lastPage = {{ (int) $features->lastPage() }};
    let loading = false;

    function renderRow(feature) {
        const id = Number(feature.id);
        const name = escapeFeatureHtml(feature.name);
        const key = escapeFeatureHtml(feature.key);
        const category = escapeFeatureHtml(feature.category);
        const type = escapeFeatureHtml(
            String(feature.type || '').replace(/^./, c => c.toUpperCase())
        );
        const initial = escapeFeatureHtml(String(feature.name || '').charAt(0).toUpperCase());
        const active = Boolean(Number(feature.is_active));
        const limitCount = Number(feature.limit_count || 0);

        return `
            <tr class="hover:bg-slate-50/70">
                <td class="px-6 py-4">
                    <div class="flex min-w-[220px] items-center gap-3">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-slate-100 text-sm font-bold text-slate-700">
                            ${initial}
                        </div>
                        <div class="min-w-0">
                            <div class="font-semibold text-slate-950">${name}</div>
                            <div class="mt-0.5 text-xs text-slate-400">${key}</div>
                        </div>
                    </div>
                </td>
                <td class="px-6 py-4 text-sm text-slate-600">${category}</td>
                <td class="px-6 py-4 text-sm text-slate-600">${type}</td>
                <td class="px-6 py-4 text-sm font-semibold text-slate-700">${limitCount}</td>
                <td class="px-6 py-4">
                    <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold ${
                        active
                            ? 'bg-emerald-50 text-emerald-700'
                            : 'bg-rose-50 text-rose-700'
                    }">
                        ${active ? 'Active' : 'Inactive'}
                    </span>
                </td>
                <td class="px-6 py-4 text-right">
                    <div class="relative inline-block text-left" data-feature-actions>
                        <button type="button"
                                onclick="toggleFeatureActions(this)"
                                class="flex h-9 w-9 items-center justify-center rounded-xl border border-slate-200 bg-white text-lg font-bold text-slate-500 hover:bg-slate-50">
                            ⋮
                        </button>
                        <div class="absolute right-0 z-30 mt-2 hidden w-44 overflow-hidden rounded-xl border border-slate-200 bg-white py-1 text-left shadow-xl"
                             data-feature-action-menu>
                            <button type="button"
                                    data-feature-load="${id}"
                                    data-feature-mode="view"
                                    class="block w-full px-4 py-2.5 text-left text-sm font-medium text-slate-700 hover:bg-slate-50">
                                View
                            </button>
                            <button type="button"
                                    data-feature-load="${id}"
                                    data-feature-mode="edit"
                                    class="block w-full px-4 py-2.5 text-left text-sm font-medium text-slate-700 hover:bg-slate-50">
                                Edit
                            </button>
                            <form method="POST"
                                  action="{{ url('/admin/core/features') }}/${id}/toggle">
                                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                <button class="block w-full px-4 py-2.5 text-left text-sm font-medium text-slate-700 hover:bg-slate-50">
                                    ${active ? 'Disable' : 'Enable'}
                                </button>
                            </form>
                        </div>
                    </div>
                </td>
            </tr>
        `;
    }

    async function loadPage(page) {
        if (loading || page < 1 || page > lastPage) return;

        loading = true;
        previous.disabled = true;
        next.disabled = true;

        try {
            const response = await fetch(
                endpoint + '?page=' + encodeURIComponent(page),
                {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                }
            );

            if (!response.ok) {
                throw new Error('Feature page request failed.');
            }

            const payload = await response.json();
            const pagination = payload.pagination || {};
            const features = Array.isArray(payload.data) ? payload.data : [];

            rows.innerHTML = features.length
                ? features.map(renderRow).join('')
                : `<tr>
                    <td colspan="6" class="px-6 py-14 text-center text-sm text-slate-500">
                        No Core features configured.
                    </td>
                   </tr>`;

            /*
             * Initial server-rendered management modals belong only to page 1.
             * AJAX page records load their management UI on demand in the next
             * feature-management endpoint step.
             */
            if (modals && page !== 1) {
                modals.innerHTML = '';
            }

            currentPage = Number(pagination.current_page || page);
            lastPage = Number(pagination.last_page || 1);

            info.textContent = pagination.total
                ? `Showing ${pagination.from}–${pagination.to} of ${pagination.total}`
                : '';

            previous.disabled = !pagination.has_previous;
            next.disabled = !pagination.has_next;
        } catch (error) {
            console.error(error);
            previous.disabled = currentPage <= 1;
            next.disabled = currentPage >= lastPage;
        } finally {
            loading = false;
        }
    }

    previous.addEventListener('click', function () {
        loadPage(currentPage - 1);
    });

    next.addEventListener('click', function () {
        loadPage(currentPage + 1);
    });
});

function togglePanel(id) {
    const panel = document.getElementById(id);
    if (!panel) return;

    panel.classList.toggle('hidden');

    if (!panel.classList.contains('hidden')) {
        panel.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
}

function toggleFeature(id) {
    const panel = document.getElementById(id);
    if (!panel) return;

    const wasHidden = panel.classList.contains('hidden');

    document.querySelectorAll('[id^="feature-"]').forEach(el => {
        if (el.id !== id && el.id.match(/^feature-\d+$/)) {
            el.classList.add('hidden');
        }
    });

    document.querySelectorAll('[id^="feature-arrow-"]').forEach(el => {
        el.style.transform = 'rotate(0deg)';
    });

    if (wasHidden) {
        panel.classList.remove('hidden');

        const featureId = id.replace('feature-', '');
        const arrow = document.getElementById('feature-arrow-' + featureId);

        if (arrow) {
            arrow.style.transform = 'rotate(180deg)';
        }
    } else {
        panel.classList.add('hidden');
    }
}

/* ESUBIZ_CORE_LIMITS_TABLE_V1 */

function openLimitModal(id) {
    document.querySelectorAll('[data-limit-modal]').forEach(modal => {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    });

    const modal = document.getElementById(id);

    if (!modal) return;

    modal.classList.remove('hidden');
    modal.classList.add('flex');

    document.querySelectorAll('[id^="limit-menu-"]').forEach(menu => {
        menu.classList.add('hidden');
    });
}

function closeLimitModal(id) {
    const modal = document.getElementById(id);

    if (!modal) return;

    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

function toggleLimitMenu(event, id) {
    event.stopPropagation();

    const target = document.getElementById(id);
    if (!target) return;

    document.querySelectorAll('[id^="limit-menu-"]').forEach(menu => {
        if (menu.id !== id) {
            menu.classList.add('hidden');
        }
    });

    target.classList.toggle('hidden');
}

const esubizTenantTableEndpoint =
    @json(route('admin.core-features.tenant-tables'));

let esubizTenantTableCache = null;
let esubizTenantTableRequest = null;

async function getEsubizTenantTables() {
    if (Array.isArray(esubizTenantTableCache)) {
        return esubizTenantTableCache;
    }

    if (esubizTenantTableRequest) {
        return esubizTenantTableRequest;
    }

    esubizTenantTableRequest = fetch(esubizTenantTableEndpoint, {
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
        .then(response => {
            if (!response.ok) {
                throw new Error('Tenant table discovery failed.');
            }

            return response.json();
        })
        .then(payload => {
            esubizTenantTableCache = Array.isArray(payload.data)
                ? payload.data
                : [];

            return esubizTenantTableCache;
        })
        .finally(() => {
            esubizTenantTableRequest = null;
        });

    return esubizTenantTableRequest;
}

function renderTenantTableOptions(picker, tables, query = '') {
    const select = picker.querySelector('[data-tenant-table-select]');
    if (!select) return;

    const current = picker.dataset.currentTable || select.value || '';
    const term = String(query || '').trim().toLowerCase();

    let filtered = tables.filter(table =>
        !term || String(table).toLowerCase().includes(term)
    );

    if (current && !filtered.includes(current)) {
        filtered = [current, ...filtered];
    }

    select.innerHTML = '';

    const placeholder = document.createElement('option');
    placeholder.value = '';
    placeholder.textContent = filtered.length
        ? 'Select tenant table'
        : 'No matching tenant tables';
    select.appendChild(placeholder);

    filtered.forEach(table => {
        const option = document.createElement('option');
        option.value = table;
        option.textContent = table;
        option.selected = table === current;
        select.appendChild(option);
    });
}

async function initialiseTenantTablePicker(picker) {
    if (!picker || picker.dataset.initialised === '1') return;

    picker.dataset.initialised = '1';

    const status = picker.querySelector('[data-tenant-table-status]');

    if (status) {
        status.textContent = 'Loading tenant tables...';
    }

    try {
        const tables = await getEsubizTenantTables();

        renderTenantTableOptions(picker, tables);

        if (status) {
            status.textContent = tables.length
                ? `${tables.length} tenant tables available.`
                : 'No tenant tables are currently available.';
        }
    } catch (error) {
        console.error(error);

        picker.dataset.initialised = '0';

        if (status) {
            status.textContent = 'Tenant tables could not be loaded.';
        }
    }
}

function initialiseTenantTablePickers(root = document) {
    root.querySelectorAll('[data-tenant-table-picker]').forEach(
        initialiseTenantTablePicker
    );
}

window.initializeTenantTablePickers = initialiseTenantTablePickers;

function toggleUsageTable(select) {
    const form = select.closest('form');
    if (!form) return;

    const field = form.querySelector('[data-usage-table-field]');
    if (!field) return;

    const tableSelect = field.querySelector(
        'select[name="usage_table"]'
    );

    const enabled = select.value === 'database_count';

    field.classList.remove('hidden');

    if (tableSelect) {
        tableSelect.disabled = !enabled;
        tableSelect.required = enabled;
    }

    if (enabled) {
        const picker = field.querySelector('[data-tenant-table-picker]');

        if (picker) {
            /*
             * Allow newly opened/newly changed forms to initialise even
             * when the picker previously existed while hidden.
             */
            if (picker.dataset.initialised !== '1') {
                initialiseTenantTablePicker(picker);
            }
        }
    }
}

function initialiseCoreLimitTables(root = document) {
    const escapeHtml = value => String(value ?? '')
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');

    const formatNumber = value =>
        new Intl.NumberFormat().format(Number(value || 0));

    root.querySelectorAll('[data-limit-table]').forEach(table => {
        const tbody = table.querySelector('tbody');
        const previous = table.querySelector('[data-limit-prev]');
        const next = table.querySelector('[data-limit-next]');
        const info = table.querySelector('[data-limit-page-info]');
        const modalContainer = table.querySelector('[data-limit-ajax-modals]');
        const ajaxUrl = table.dataset.ajaxUrl;

        let page = 1;
        let lastPage = 1;
        let loading = false;

        if (!tbody || !ajaxUrl) return;

        function updateControls(pagination) {
            page = Number(pagination.current_page || 1);
            lastPage = Number(pagination.last_page || 1);

            if (info) {
                info.textContent = Number(pagination.total || 0) === 0
                    ? '0 limits'
                    : `Showing ${pagination.from}–${pagination.to} of ${pagination.total}`;
            }

            if (previous) {
                previous.disabled = loading || !pagination.has_previous;
                previous.classList.toggle(
                    'opacity-50',
                    previous.disabled
                );
                previous.classList.toggle(
                    'cursor-not-allowed',
                    previous.disabled
                );
            }

            if (next) {
                next.disabled = loading || !pagination.has_next;
                next.classList.toggle(
                    'opacity-50',
                    next.disabled
                );
                next.classList.toggle(
                    'cursor-not-allowed',
                    next.disabled
                );
            }
        }

        function buildRow(limit) {
            const usage =
                limit.resource_usage
                && typeof limit.resource_usage === 'object'
                    ? limit.resource_usage
                    : {};
            const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
            const toggleUrlTemplate =
                @json(route('admin.core-features.limits.toggle', ['id' => '__LIMIT_ID__']));
            const toggleUrl = toggleUrlTemplate.replace('__LIMIT_ID__', Number(limit.id));

            const isUnlimited =
                Boolean(Number(limit.is_unlimited)) ||
                limit.value_type === 'unlimited';

            const status = Boolean(Number(limit.is_active))
                ? `<span class="inline-flex rounded-full bg-blue-50 px-2.5 py-1 text-xs font-semibold text-blue-700">Active</span>`
                : `<span class="inline-flex rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-500">Inactive</span>`;

            const usageSource = usage.driver === 'database_count'
                ? `<span class="font-medium">Database Count</span>${
                    usage.table
                        ? `<span class="block text-xs text-slate-400">${escapeHtml(usage.table)}</span>`
                        : ''
                }`
                : `<span class="text-slate-400">Not configured</span>`;

            return `
                <tr data-limit-row>
                    <td class="whitespace-nowrap px-4 py-4">
                        <div class="text-sm font-semibold text-slate-900">
                            ${escapeHtml(limit.name)}
                        </div>
                    </td>

                    <td class="whitespace-nowrap px-4 py-4">
                        <code class="rounded-lg bg-slate-100 px-2 py-1 text-xs text-slate-600">
                            ${escapeHtml(limit.limit_key)}
                        </code>
                    </td>

                    <td class="whitespace-nowrap px-4 py-4 text-sm text-slate-600">
                        ${escapeHtml(
                            String(limit.value_type || '')
                                .replace(/^./, c => c.toUpperCase())
                        )}
                    </td>

                    <td class="whitespace-nowrap px-4 py-4 text-sm font-semibold text-slate-900">
                        ${isUnlimited
                            ? 'Unlimited'
                            : formatNumber(limit.default_value)}
                    </td>

                    <td class="whitespace-nowrap px-4 py-4 text-sm text-slate-600">
                        ${limit.unit ? escapeHtml(limit.unit) : '—'}
                    </td>

                    <td class="whitespace-nowrap px-4 py-4 text-sm text-slate-600">
                        ${usageSource}
                    </td>

                    <td class="whitespace-nowrap px-4 py-4">
                        ${status}
                    </td>

                    <td class="relative whitespace-nowrap px-4 py-4 text-right">
                        <button type="button"
                            onclick="toggleLimitMenu(event, 'limit-menu-${Number(limit.id)}')"
                            class="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-slate-200 bg-white text-lg font-bold text-slate-500 hover:bg-slate-50">
                            ⋮
                        </button>

                        <div id="limit-menu-${Number(limit.id)}"
                            class="absolute right-4 z-30 mt-2 hidden w-44 overflow-hidden rounded-xl border border-slate-200 bg-white py-1 text-left shadow-xl">

                            <button type="button"
                                onclick="openLimitModal('limit-view-${Number(limit.id)}')"
                                class="block w-full px-4 py-2.5 text-left text-sm font-medium text-slate-700 hover:bg-slate-50">
                                View
                            </button>

                            <button type="button"
                                onclick="openLimitModal('limit-edit-${Number(limit.id)}')"
                                class="block w-full px-4 py-2.5 text-left text-sm font-medium text-slate-700 hover:bg-slate-50">
                                Edit
                            </button>

                            <form method="POST"
                                action="${escapeHtml(toggleUrl)}">
                                <input type="hidden" name="_token" value="${escapeHtml(csrf)}">
                                <button type="submit"
                                    class="block w-full px-4 py-2.5 text-left text-sm font-medium text-slate-700 hover:bg-slate-50">
                                    ${Boolean(Number(limit.is_active)) ? 'Disable' : 'Enable'}
                                </button>
                            </form>

                            <form method="POST"
                                action="${escapeHtml(
                                    @json(route('admin.core-features.limits.destroy', ['id' => '__LIMIT_ID__']))
                                        .replace('__LIMIT_ID__', Number(limit.id))
                                )}"
                                onsubmit="return confirm('Soft delete this Core limit?');">
                                <input type="hidden" name="_token" value="${escapeHtml(csrf)}">
                                <button type="submit"
                                    class="block w-full px-4 py-2.5 text-left text-sm font-semibold text-red-600 hover:bg-red-50">
                                    Delete
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            `;
        }

        function buildModals(limit) {
            const usage =
                limit.resource_usage
                && typeof limit.resource_usage === 'object'
                    ? limit.resource_usage
                    : {};
            const usageDriver = usage.driver || '';
            const usageTable = usage.table || '';
            const isUnlimited =
                Boolean(Number(limit.is_unlimited)) ||
                limit.value_type === 'unlimited';

            const id = Number(limit.id);
            const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';

            const types = [
                'quantity',
                'boolean',
                'storage',
                'bandwidth',
                'credits',
                'unlimited'
            ];

            const typeOptions = types.map(type => `
                <option value="${type}" ${limit.value_type === type ? 'selected' : ''}>
                    ${type.charAt(0).toUpperCase() + type.slice(1)}
                </option>
            `).join('');

            const updateUrlTemplate =
                @json(route('admin.core-features.limits.update', ['id' => '__LIMIT_ID__']));

            const toggleUrlTemplate =
                @json(route('admin.core-features.limits.toggle', ['id' => '__LIMIT_ID__']));

            const updateUrl = updateUrlTemplate.replace('__LIMIT_ID__', id);
            const toggleUrl = toggleUrlTemplate.replace('__LIMIT_ID__', id);

            return `
                <div id="limit-view-${id}"
                    class="fixed inset-0 z-[80] hidden items-center justify-center bg-slate-950/50 p-4"
                    data-limit-modal>
                    <div class="w-full max-w-xl rounded-3xl bg-white p-6 shadow-2xl">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="text-[11px] font-bold uppercase tracking-[0.14em] text-blue-600">
                                    Core Limit
                                </p>
                                <h3 class="mt-1 text-xl font-bold text-slate-900">
                                    ${escapeHtml(limit.name)}
                                </h3>
                            </div>
                            <button type="button"
                                onclick="closeLimitModal('limit-view-${id}')"
                                class="text-2xl text-slate-400">×</button>
                        </div>

                        <dl class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <dt class="text-xs font-semibold uppercase text-slate-400">Key</dt>
                                <dd class="mt-1 text-sm font-medium text-slate-900">
                                    ${escapeHtml(limit.limit_key)}
                                </dd>
                            </div>

                            <div>
                                <dt class="text-xs font-semibold uppercase text-slate-400">Type</dt>
                                <dd class="mt-1 text-sm font-medium text-slate-900">
                                    ${escapeHtml(
                                        String(limit.value_type || '')
                                            .replace(/^./, c => c.toUpperCase())
                                    )}
                                </dd>
                            </div>

                            <div>
                                <dt class="text-xs font-semibold uppercase text-slate-400">SaaS Limit</dt>
                                <dd class="mt-1 text-sm font-medium text-slate-900">
                                    ${isUnlimited
                                        ? 'Unlimited'
                                        : formatNumber(limit.default_value)}
                                </dd>
                            </div>

                            <div>
                                <dt class="text-xs font-semibold uppercase text-slate-400">Unit</dt>
                                <dd class="mt-1 text-sm font-medium text-slate-900">
                                    ${limit.unit ? escapeHtml(limit.unit) : '—'}
                                </dd>
                            </div>

                            <div class="sm:col-span-2">
                                <dt class="text-xs font-semibold uppercase text-slate-400">Usage Source</dt>
                                <dd class="mt-1 text-sm font-medium text-slate-900">
                                    ${usageDriver === 'database_count'
                                        ? `Database Count — ${escapeHtml(usageTable || 'Not configured')}`
                                        : 'Not configured'}
                                </dd>
                            </div>
                        </dl>
                    </div>
                </div>

                <div id="limit-edit-${id}"
                    class="fixed inset-0 z-[80] hidden items-center justify-center bg-slate-950/50 p-4"
                    data-limit-modal>
                    <div class="w-full max-w-2xl rounded-3xl bg-white p-6 shadow-2xl">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="text-[11px] font-bold uppercase tracking-[0.14em] text-blue-600">
                                    Edit Core Limit
                                </p>
                                <h3 class="mt-1 text-xl font-bold text-slate-900">
                                    ${escapeHtml(limit.name)}
                                </h3>
                            </div>

                            <button type="button"
                                onclick="closeLimitModal('limit-edit-${id}')"
                                class="text-2xl text-slate-400">×</button>
                        </div>

                        <form method="POST"
                            action="${escapeHtml(updateUrl)}"
                            class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-2">

                            <input type="hidden" name="_token" value="${escapeHtml(csrf)}">

                            <div>
                                <label class="mb-1 block text-xs font-semibold text-slate-600">
                                    Limit Name
                                </label>
                                <input name="name"
                                    value="${escapeHtml(limit.name)}"
                                    required
                                    class="esubiz-admin-input">
                            </div>

                            <div>
                                <label class="mb-1 block text-xs font-semibold text-slate-600">
                                    Type
                                </label>
                                <select name="value_type" class="esubiz-admin-input">
                                    ${typeOptions}
                                </select>
                            </div>

                            <div>
                                <label class="mb-1 block text-xs font-semibold text-slate-600">
                                    Default SaaS Limit
                                </label>
                                <input name="default_value"
                                    type="number"
                                    min="0"
                                    value="${escapeHtml(limit.default_value ?? '')}"
                                    class="esubiz-admin-input">
                            </div>

                            <div>
                                <label class="mb-1 block text-xs font-semibold text-slate-600">
                                    Unit
                                </label>
                                <input name="unit"
                                    value="${escapeHtml(limit.unit ?? '')}"
                                    placeholder="records"
                                    class="esubiz-admin-input">
                            </div>

                            <div>
                                <label class="mb-1 block text-xs font-semibold text-slate-600">
                                    Usage Source
                                </label>
                                <select name="usage_driver"
                                    onchange="toggleUsageTable(this)"
                                    class="esubiz-admin-input">
                                    <option value="">None</option>
                                    <option value="database_count"
                                        ${usageDriver === 'database_count' ? 'selected' : ''}>
                                        Database Count
                                    </option>
                                </select>
                            </div>

                            <div data-usage-table-field>
                                <label class="mb-1 block text-xs font-semibold text-slate-600">
                                    Tenant Table
                                </label>

                                <div data-tenant-table-picker
                                    data-current-table="${escapeHtml(usageTable)}">

                                    <select name="usage_table"
                                        ${usageDriver === 'database_count' ? 'required' : 'disabled'}
                                        class="esubiz-admin-input"
                                        data-tenant-table-select>
                                        ${
                                            usageTable
                                                ? `<option value="${escapeHtml(usageTable)}" selected>${escapeHtml(usageTable)}</option>`
                                                : '<option value="">Select tenant table</option>'
                                        }
                                    </select>

                                    <p class="mt-1 text-xs text-slate-400"
                                        data-tenant-table-status>
                                        Tables load automatically from the current Core schema.
                                    </p>
                                </div>

                                <input type="hidden"
                                    name="usage_connection"
                                    value="tenant">
                            </div>

                            <div class="flex justify-end gap-3 sm:col-span-2">
                                <button type="button"
                                    onclick="closeLimitModal('limit-edit-${id}')"
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
            `;
        }

        async function loadPage(targetPage) {
            if (loading || targetPage < 1 || targetPage > lastPage) return;

            loading = true;

            if (previous) previous.disabled = true;
            if (next) next.disabled = true;

            try {
                const separator = ajaxUrl.includes('?') ? '&' : '?';

                const response = await fetch(
                    `${ajaxUrl}${separator}page=${targetPage}`,
                    {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        credentials: 'same-origin'
                    }
                );

                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}`);
                }

                const payload = await response.json();

                tbody.innerHTML = payload.data.length
                    ? payload.data.map(buildRow).join('')
                    : `
                        <tr data-limit-empty>
                            <td colspan="8"
                                class="px-5 py-12 text-center text-sm text-slate-500">
                                No limits configured for this feature.
                            </td>
                        </tr>
                    `;

                if (modalContainer) {
                    modalContainer.innerHTML = payload.data
                        .map(buildModals)
                        .join('');

                    initialiseTenantTablePickers(modalContainer);
                }

                updateControls(payload.pagination);
            } catch (error) {
                console.error('Core Limits AJAX pagination failed:', error);
            } finally {
                loading = false;

                previous?.classList.remove('opacity-50', 'cursor-not-allowed');
                next?.classList.remove('opacity-50', 'cursor-not-allowed');

                if (previous) {
                    previous.disabled = page <= 1;
                    previous.classList.toggle('opacity-50', previous.disabled);
                    previous.classList.toggle('cursor-not-allowed', previous.disabled);
                }

                if (next) {
                    next.disabled = page >= lastPage;
                    next.classList.toggle('opacity-50', next.disabled);
                    next.classList.toggle('cursor-not-allowed', next.disabled);
                }
            }
        }

        previous?.addEventListener('click', () => loadPage(page - 1));
        next?.addEventListener('click', () => loadPage(page + 1));

        /*
         * Fetch page 1 once so pagination metadata reflects the true
         * database total rather than only the 10 initial Blade rows.
         */
        loadPage(1);
    });

    document.querySelectorAll('select[name="usage_driver"]').forEach(select => {
        toggleUsageTable(select);
    });
}

document.addEventListener('click', () => {
    document.querySelectorAll('[id^="limit-menu-"]').forEach(menu => {
        menu.classList.add('hidden');
    });
});

document.addEventListener('keydown', event => {
    if (event.key !== 'Escape') return;

    document.querySelectorAll('[data-limit-modal]').forEach(modal => {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    });
});

window.initializeCoreLimitTables = initialiseCoreLimitTables;

document.addEventListener('DOMContentLoaded', function () {
    initialiseCoreLimitTables(document);
    initialiseTenantTablePickers(document);
});

</script>

@endsection
