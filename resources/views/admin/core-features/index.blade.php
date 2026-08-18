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
    <div class="space-y-4">

        <div class="flex items-end justify-between">
            <div>
                <h2 class="text-lg font-semibold text-slate-950">
                    Core Feature Registry
                </h2>
                <p class="mt-1 text-sm text-slate-500">
                    {{ $features->count() }} configured features
                </p>
            </div>
        </div>

        @forelse($features as $feature)

            <div class="esubiz-admin-card overflow-hidden">

                {{-- FEATURE SUMMARY --}}
                <button type="button"
                    onclick="toggleFeature('feature-{{ $feature->id }}')"
                    class="w-full px-6 py-5 text-left transition hover:bg-slate-50">
                    <div class="flex items-center justify-between gap-5">

                        <div class="flex min-w-0 items-center gap-4">

                            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-slate-100 text-sm font-bold text-slate-700">
                                {{ strtoupper(substr($feature->name, 0, 1)) }}
                            </div>

                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <h3 class="font-semibold text-slate-950">
                                        {{ $feature->name }}
                                    </h3>

                                    <x-admin.badge type="info">
                                        {{ $feature->category }}
                                    </x-admin.badge>

                                    @if($feature->is_active)
                                        <x-admin.badge type="success">Active</x-admin.badge>
                                    @else
                                        <x-admin.badge type="danger">Inactive</x-admin.badge>
                                    @endif
                                </div>

                                <p class="mt-1 text-xs font-medium text-slate-400">
                                    {{ $feature->key }}
                                </p>
                            </div>

                        </div>

                        <div class="flex shrink-0 items-center gap-4">
                            <span class="hidden text-xs text-slate-400 sm:block">
                                {{ $limits->get($feature->id, collect())->count() }}
                                {{ $limits->get($feature->id, collect())->count() === 1 ? 'limit' : 'limits' }}
                            </span>

                            <span id="feature-arrow-{{ $feature->id }}"
                                class="flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 text-slate-500 transition">
                                ↓
                            </span>
                        </div>

                    </div>
                </button>

                {{-- EDIT PANEL --}}
                <div id="feature-{{ $feature->id }}"
                    class="hidden border-t border-slate-100 bg-slate-50/50">

                    <div class="p-6">

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
                        <div class="my-7 flex items-center gap-3">
                            <div class="h-px flex-1 bg-slate-200"></div>
                            <span class="text-[11px] font-bold uppercase tracking-[0.16em] text-slate-400">
                                Usage Limits
                            </span>
                            <div class="h-px flex-1 bg-slate-200"></div>
                        </div>

                        <div class="space-y-3">

                            @forelse($limits->get($feature->id, collect()) as $limit)

                                <form method="POST"
                                    action="{{ route('admin.core-features.limits.update', $limit->id) }}"
                                    class="rounded-2xl border border-slate-200 bg-white p-4">
                                    @csrf

                                    <div class="grid grid-cols-1 gap-4 md:grid-cols-5 md:items-end">

                                        <div>
                                            <label class="mb-1 block text-[11px] font-semibold uppercase tracking-wider text-slate-400">
                                                Limit
                                            </label>
                                            <input name="name"
                                                value="{{ $limit->name }}"
                                                required
                                                class="esubiz-admin-input">
                                        </div>

                                        <div>
                                            <label class="mb-1 block text-[11px] font-semibold uppercase tracking-wider text-slate-400">
                                                Type
                                            </label>
                                            <select name="value_type" class="esubiz-admin-input">
                                                @foreach(['quantity','boolean','storage','bandwidth','credits','unlimited'] as $type)
                                                    <option value="{{ $type }}" @selected($limit->value_type === $type)>
                                                        {{ ucfirst($type) }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div>
                                            <label class="mb-1 block text-[11px] font-semibold uppercase tracking-wider text-slate-400">
                                                Default
                                            </label>
                                            <input name="default_value"
                                                type="number"
                                                min="0"
                                                value="{{ $limit->default_value }}"
                                                class="esubiz-admin-input">
                                        </div>

                                        <div>
                                            <label class="mb-1 block text-[11px] font-semibold uppercase tracking-wider text-slate-400">
                                                Unit
                                            </label>
                                            <input name="unit"
                                                value="{{ $limit->unit }}"
                                                placeholder="records"
                                                class="esubiz-admin-input">
                                        </div>

                                        <button class="esubiz-admin-secondary">
                                            Save Limit
                                        </button>

                                    </div>
                                </form>

                            @empty
                                <div class="rounded-2xl border border-dashed border-slate-300 bg-white px-5 py-8 text-center">
                                    <p class="text-sm font-medium text-slate-600">
                                        No limits configured for this feature.
                                    </p>
                                </div>
                            @endforelse

                        </div>

                        {{-- ADD LIMIT --}}
                        <form method="POST"
                            action="{{ route('admin.core-features.limits.store', $feature->id) }}"
                            class="mt-4 rounded-2xl border border-dashed border-slate-300 bg-white p-5">
                            @csrf

                            <div class="mb-4">
                                <h5 class="text-sm font-semibold text-slate-900">
                                    Add Limit
                                </h5>
                                <p class="mt-1 text-xs text-slate-500">
                                    Define another measurable entitlement for this feature.
                                </p>
                            </div>

                            <div class="grid grid-cols-1 gap-3 md:grid-cols-4">

                                <input name="limit_key"
                                    required
                                    placeholder="limit_key"
                                    class="esubiz-admin-input">

                                <input name="name"
                                    required
                                    placeholder="Limit name"
                                    class="esubiz-admin-input">

                                <select name="value_type" class="esubiz-admin-input">
                                    <option value="quantity">Quantity</option>
                                    <option value="boolean">Boolean</option>
                                    <option value="storage">Storage</option>
                                    <option value="bandwidth">Bandwidth</option>
                                    <option value="credits">Credits</option>
                                    <option value="unlimited">Unlimited</option>
                                </select>

                                <button class="esubiz-admin-primary">
                                    + Add Limit
                                </button>

                            </div>

                            <input name="default_value"
                                type="hidden"
                                value="0">

                        </form>

                    </div>
                </div>

            </div>

        @empty

            <x-admin.card>
                <div class="py-16 text-center">
                    <div class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-100 text-slate-400">
                        ⚙
                    </div>
                    <h3 class="font-semibold text-slate-900">
                        No Core features yet
                    </h3>
                    <p class="mt-1 text-sm text-slate-500">
                        Click “Add Feature” to create your first Core capability.
                    </p>
                </div>
            </x-admin.card>

        @endforelse

    </div>

</x-admin.ui>

<script>
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
</script>

@endsection
