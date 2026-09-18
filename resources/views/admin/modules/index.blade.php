@extends('layouts.admin')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <h1 class="h3 mb-1">Modules</h1>
            <p class="text-muted mb-0">
                Manage Esubiz Marketplace modules and their deployment configuration.
            </p>
        </div>

        <a href="{{ route('admin.modules.create') }}" class="btn btn-primary">
            Create Module
        </a>
    </div>

    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <div class="card" id="modules-table-container">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Module</th>
                            <th>Version</th>
                            <th>Deployment</th>
                            <th>Sidebar Menu</th>
                            <th>Marketplace</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($modules as $module)
                            @php
                                $catalog = $module->catalogProduct;
                                $audience = $catalog?->audience ?? null;

                                $deployment = match ($audience) {
                                    'both' => 'SaaS + Off-server',
                                    'saas' => 'SaaS',
                                    'developer' => 'Off-server',
                                    default => '—',
                                };
                            @endphp

                            <tr>
                                <td>
                                    <div class="fw-semibold">
                                        {{ $module->name }}
                                    </div>

                                    <div class="small text-muted">
                                        {{ $module->slug }}
                                    </div>
                                </td>

                                <td>
                                    {{ $module->version }}
                                </td>

                                <td>
                                    {{ $deployment }}
                                </td>

                                <td>
                                    {{ $module->sidebar_menu_name ?: '—' }}
                                </td>

                                <td>
                                    @if ($catalog?->is_public)
                                        <span class="badge bg-success">
                                            Public
                                        </span>
                                    @else
                                        <span class="badge bg-secondary">
                                            Private
                                        </span>
                                    @endif

                                    @if ($catalog?->is_featured)
                                        <span class="badge bg-info">
                                            Featured
                                        </span>
                                    @endif
                                </td>

                                <td>
                                    @if ($module->is_active && $catalog?->is_active)
                                        <span class="badge bg-success">
                                            Active
                                        </span>
                                    @else
                                        <span class="badge bg-secondary">
                                            Inactive
                                        </span>
                                    @endif
                                </td>

                                <td class="text-end">
                                    <a
                                        href="{{ route('admin.modules.edit', $module) }}"
                                        class="btn btn-sm btn-outline-primary"
                                    >
                                        Edit
                                    </a>

                                    <form
                                        method="POST"
                                        action="{{ route('admin.modules.destroy', $module) }}"
                                        class="d-inline"
                                        onsubmit="return confirm('Remove this module from Central Marketplace?');"
                                    >
                                        @csrf
                                        @method('DELETE')

                                        <button
                                            type="submit"
                                            class="btn btn-sm btn-outline-danger"
                                        >
                                            Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <div class="text-muted mb-3">
                                        No modules have been created yet.
                                    </div>

                                    <a
                                        href="{{ route('admin.modules.create') }}"
                                        class="btn btn-primary"
                                    >
                                        Create First Module
                                    </a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card-footer d-flex align-items-center justify-content-between">
            <div class="small text-muted">
                Showing {{ $modules->firstItem() ?? 0 }}–{{ $modules->lastItem() ?? 0 }}
                of {{ $modules->total() }}
            </div>

            <div class="d-flex gap-2">
                <button
                    type="button"
                    class="btn btn-sm btn-outline-secondary js-modules-page"
                    data-url="{{ $modules->previousPageUrl() }}"
                    @disabled(!$modules->previousPageUrl())
                >
                    Previous
                </button>

                <button
                    type="button"
                    class="btn btn-sm btn-outline-secondary js-modules-page"
                    data-url="{{ $modules->nextPageUrl() }}"
                    @disabled(!$modules->nextPageUrl())
                >
                    Next
                </button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('click', async function (event) {
    const button = event.target.closest('.js-modules-page');

    if (!button || button.disabled) {
        return;
    }

    const url = button.dataset.url;

    if (!url) {
        return;
    }

    const container = document.getElementById('modules-table-container');

    button.disabled = true;

    try {
        const response = await fetch(url, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        });

        if (!response.ok) {
            throw new Error('Unable to load modules.');
        }

        const html = await response.text();
        const documentFragment = new DOMParser()
            .parseFromString(html, 'text/html');

        const replacement = documentFragment
            .getElementById('modules-table-container');

        if (!replacement) {
            throw new Error('Modules table was not returned.');
        }

        container.replaceWith(replacement);

        window.history.replaceState(
            {},
            '',
            url
        );
    } catch (error) {
        button.disabled = false;
        console.error(error);
    }
});
</script>
@endsection
