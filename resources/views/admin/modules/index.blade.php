@extends('admin.layouts.app')

@section('content')
<style>
.esu-modules-page{
    --esu-blue:#2563eb;
    --esu-border:#e5e7eb;
    --esu-muted:#64748b;
    --esu-text:#0f172a;
    --esu-soft:#f8fafc;
}

.esu-modules-page .esu-page-head{
    display:flex;
    align-items:flex-start;
    justify-content:space-between;
    gap:20px;
    margin-bottom:24px;
}

.esu-modules-page .esu-page-title{
    margin:0;
    color:var(--esu-text);
    font-size:28px;
    font-weight:750;
    letter-spacing:-.03em;
}

.esu-modules-page .esu-page-copy{
    margin:6px 0 0;
    color:var(--esu-muted);
    font-size:14px;
}

.esu-modules-page .esu-primary-btn{
    display:inline-flex;
    align-items:center;
    justify-content:center;
    gap:8px;
    min-height:42px;
    padding:0 17px;
    border:1px solid var(--esu-blue);
    border-radius:11px;
    background:var(--esu-blue);
    color:#fff;
    font-size:14px;
    font-weight:650;
    text-decoration:none;
    box-shadow:0 5px 14px rgba(37,99,235,.16);
}

.esu-modules-page .esu-card{
    overflow:visible;
    border:1px solid var(--esu-border);
    border-radius:16px;
    background:#fff;
    box-shadow:0 8px 30px rgba(15,23,42,.045);
}

.esu-modules-page .esu-table-wrap{
    overflow-x:auto;
    border-radius:16px 16px 0 0;
}

.esu-modules-page .esu-table{
    width:100%;
    margin:0;
    border-collapse:separate;
    border-spacing:0;
}

.esu-modules-page .esu-table th{
    padding:14px 18px;
    border-bottom:1px solid var(--esu-border);
    background:var(--esu-soft);
    color:#475569;
    font-size:11px;
    font-weight:750;
    letter-spacing:.055em;
    text-transform:uppercase;
    white-space:nowrap;
}

.esu-modules-page .esu-table td{
    padding:16px 18px;
    border-bottom:1px solid #eef2f7;
    color:#334155;
    font-size:14px;
    vertical-align:middle;
}

.esu-modules-page .esu-table tbody tr:last-child td{
    border-bottom:0;
}

.esu-modules-page .esu-module-name{
    color:var(--esu-text);
    font-weight:700;
}

.esu-modules-page .esu-module-slug{
    margin-top:3px;
    color:#94a3b8;
    font-size:12px;
}

.esu-modules-page .esu-badges{
    display:flex;
    flex-wrap:wrap;
    gap:6px;
}

.esu-modules-page .esu-badge{
    display:inline-flex;
    align-items:center;
    min-height:24px;
    padding:3px 8px;
    border-radius:999px;
    font-size:11px;
    font-weight:700;
}

.esu-modules-page .esu-badge-blue{
    background:#eff6ff;
    color:#2563eb;
}

.esu-modules-page .esu-badge-slate{
    background:#f1f5f9;
    color:#64748b;
}

.esu-modules-page .esu-badge-amber{
    background:#fffbeb;
    color:#b45309;
}

/* Standard Esubiz state toggle */
.esu-modules-page .esu-toggle{
    position:relative;
    display:inline-flex;
    width:42px;
    height:23px;
    flex:none;
    border-radius:999px;
    background:#cbd5e1;
    vertical-align:middle;
}

.esu-modules-page .esu-toggle::after{
    content:"";
    position:absolute;
    top:3px;
    left:3px;
    width:17px;
    height:17px;
    border-radius:50%;
    background:#fff;
    box-shadow:0 1px 3px rgba(15,23,42,.25);
    transition:.18s ease;
}

.esu-modules-page .esu-toggle.is-active{
    background:var(--esu-blue);
}

.esu-modules-page .esu-toggle.is-active::after{
    transform:translateX(19px);
}

.esu-modules-page .esu-status{
    display:flex;
    align-items:center;
    gap:9px;
    white-space:nowrap;
}

.esu-modules-page .esu-status-text{
    color:#64748b;
    font-size:12px;
    font-weight:650;
}

.esu-modules-page .esu-actions{
    position:relative;
    display:flex;
    justify-content:flex-end;
}

.esu-modules-page .esu-more-btn{
    display:inline-flex;
    align-items:center;
    justify-content:center;
    width:36px;
    height:36px;
    padding:0;
    border:1px solid var(--esu-border);
    border-radius:10px;
    background:#fff;
    color:#475569;
    font-size:20px;
    font-weight:700;
    line-height:1;
    cursor:pointer;
}

.esu-modules-page .esu-more-btn:hover{
    background:#f8fafc;
    color:#0f172a;
}

.esu-modules-page .esu-menu{
    position:absolute;
    z-index:50;
    top:41px;
    right:0;
    display:none;
    width:190px;
    overflow:hidden;
    border:1px solid var(--esu-border);
    border-radius:12px;
    background:#fff;
    box-shadow:0 16px 38px rgba(15,23,42,.14);
}

.esu-modules-page .esu-menu.is-open{
    display:block;
}

.esu-modules-page .esu-menu a,
.esu-modules-page .esu-menu button{
    display:flex;
    width:100%;
    align-items:center;
    padding:11px 13px;
    border:0;
    background:#fff;
    color:#334155;
    font-size:13px;
    font-weight:550;
    text-align:left;
    text-decoration:none;
}

.esu-modules-page .esu-menu a:hover,
.esu-modules-page .esu-menu button:hover{
    background:#f8fafc;
}

.esu-modules-page .esu-menu .esu-danger{
    color:#dc2626;
}

.esu-modules-page .esu-menu-divider{
    height:1px;
    background:#eef2f7;
}

.esu-modules-page .esu-footer{
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:16px;
    padding:14px 18px;
    border-top:1px solid var(--esu-border);
}

.esu-modules-page .esu-count{
    color:var(--esu-muted);
    font-size:12px;
}

.esu-modules-page .esu-pagination{
    display:flex;
    gap:8px;
}

.esu-modules-page .esu-page-btn{
    min-width:82px;
    min-height:36px;
    padding:0 13px;
    border:1px solid #dbe2ea;
    border-radius:9px;
    background:#fff;
    color:#475569;
    font-size:12px;
    font-weight:650;
}

.esu-modules-page .esu-page-btn:not(:disabled):hover{
    border-color:#bfdbfe;
    background:#eff6ff;
    color:#2563eb;
}

.esu-modules-page .esu-page-btn:disabled{
    cursor:not-allowed;
    opacity:.45;
}

.esu-modules-page .esu-empty{
    padding:60px 20px !important;
    text-align:center;
}

.esu-modules-page .esu-empty-title{
    margin-bottom:6px;
    color:#334155;
    font-weight:700;
}

.esu-modules-page .esu-empty-copy{
    margin-bottom:18px;
    color:#94a3b8;
    font-size:13px;
}

@media (max-width:767px){
    .esu-modules-page .esu-page-head{
        align-items:stretch;
        flex-direction:column;
    }

    .esu-modules-page .esu-primary-btn{
        width:100%;
    }

    .esu-modules-page .esu-footer{
        align-items:stretch;
        flex-direction:column;
    }

    .esu-modules-page .esu-pagination{
        width:100%;
    }

    .esu-modules-page .esu-page-btn{
        flex:1;
    }
}
</style>

<div class="container-fluid py-4 esu-modules-page">
    <div class="esu-page-head">
        <div>
            <h1 class="esu-page-title">Modules</h1>
            <p class="esu-page-copy">
                Manage Marketplace modules, compatibility and Core integration.
            </p>
        </div>

        <a href="{{ route('admin.modules.create') }}" class="esu-primary-btn">
            <span>＋</span>
            <span>Add Module</span>
        </a>
    </div>

    @if (session('success'))
        <div class="alert alert-success mb-4">
            {{ session('success') }}
        </div>
    @endif

    <div class="esu-card" id="modules-table-container">
        <div class="esu-table-wrap">
            <table class="esu-table">
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

                            $active =
                                (bool) $module->is_active
                                && (bool) $catalog?->is_active;
                        @endphp

                        <tr>
                            <td>
                                <div class="esu-module-name">
                                    {{ $module->name }}
                                </div>

                                <div class="esu-module-slug">
                                    {{ $module->slug }}
                                </div>
                            </td>

                            <td>{{ $module->version }}</td>

                            <td>{{ $deployment }}</td>

                            <td>
                                {{ $module->sidebar_menu_name ?: '—' }}
                            </td>

                            <td>
                                <div class="esu-badges">
                                    <span class="esu-badge {{ $catalog?->is_public ? 'esu-badge-blue' : 'esu-badge-slate' }}">
                                        {{ $catalog?->is_public ? 'Public' : 'Private' }}
                                    </span>

                                    @if ($catalog?->is_featured)
                                        <span class="esu-badge esu-badge-amber">
                                            Featured
                                        </span>
                                    @endif
                                </div>
                            </td>

                            <td>
                                <div class="esu-status">
                                    <span class="esu-toggle {{ $active ? 'is-active' : '' }}"></span>
                                    <span class="esu-status-text">
                                        {{ $active ? 'Active' : 'Inactive' }}
                                    </span>
                                </div>
                            </td>

                            <td>
                                <div class="esu-actions">
                                    <button
                                        type="button"
                                        class="esu-more-btn js-module-menu-toggle"
                                        aria-label="Module actions"
                                        aria-expanded="false"
                                    >⋮</button>

                                    <div class="esu-menu">
                                        <a href="{{ route('admin.modules.edit', $module) }}">
                                            Edit Module
                                        </a>

                                        <div class="esu-menu-divider"></div>

                                        <form
                                            method="POST"
                                            action="{{ route('admin.modules.destroy', $module) }}"
                                            onsubmit="return confirm('Remove this module from Central Marketplace?');"
                                        >
                                            @csrf
                                            @method('DELETE')

                                            <button type="submit" class="esu-danger">
                                                Delete Module
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="esu-empty">
                                <div class="esu-empty-title">
                                    No modules yet
                                </div>

                                <div class="esu-empty-copy">
                                    Add your first Marketplace module to begin.
                                </div>

                                <a
                                    href="{{ route('admin.modules.create') }}"
                                    class="esu-primary-btn"
                                >
                                    Add Module
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="esu-footer">
            <div class="esu-count">
                Showing {{ $modules->firstItem() ?? 0 }}–{{ $modules->lastItem() ?? 0 }}
                of {{ $modules->total() }}
            </div>

            <div class="esu-pagination">
                <button
                    type="button"
                    class="esu-page-btn js-modules-page"
                    data-url="{{ $modules->previousPageUrl() }}"
                    @disabled(!$modules->previousPageUrl())
                >
                    Previous
                </button>

                <button
                    type="button"
                    class="esu-page-btn js-modules-page"
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
    const menuButton = event.target.closest('.js-module-menu-toggle');

    if (menuButton) {
        const menu = menuButton.nextElementSibling;
        const opening = !menu.classList.contains('is-open');

        document.querySelectorAll('.esu-menu.is-open').forEach(function (item) {
            item.classList.remove('is-open');
        });

        document.querySelectorAll('.js-module-menu-toggle').forEach(function (item) {
            item.setAttribute('aria-expanded', 'false');
        });

        if (opening) {
            menu.classList.add('is-open');
            menuButton.setAttribute('aria-expanded', 'true');
        }

        return;
    }

    if (!event.target.closest('.esu-menu')) {
        document.querySelectorAll('.esu-menu.is-open').forEach(function (item) {
            item.classList.remove('is-open');
        });

        document.querySelectorAll('.js-module-menu-toggle').forEach(function (item) {
            item.setAttribute('aria-expanded', 'false');
        });
    }

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

        const parsed = new DOMParser()
            .parseFromString(html, 'text/html');

        const replacement = parsed
            .getElementById('modules-table-container');

        if (!replacement) {
            throw new Error('Modules table was not returned.');
        }

        container.replaceWith(replacement);

        window.history.replaceState({}, '', url);
    } catch (error) {
        button.disabled = false;
        console.error(error);
    }
});
</script>
@endsection
