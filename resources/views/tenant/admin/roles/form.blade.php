@extends('tenant.admin.layouts.app')

@section('content')
@php
    $editing = (bool) $role;
    $isSystem = $editing && (bool) $role->is_system;
    $isAdministrator = $editing && $role->slug === 'administrator';
@endphp

<div style="max-width:900px;margin:0 auto;padding:24px;">
    <div style="margin-bottom:20px;">
        @coreCan('roles.view')
<a
            href="/admin/users/roles"
            style="text-decoration:none;color:#6b7280;font-size:14px;"
        >
            ← Roles &amp; Permissions
        </a>
@endcoreCan

        <h1 style="margin:10px 0 4px;font-size:28px;font-weight:700;color:#111827;">
            {{ $editing ? 'Edit Role' : 'Create Role' }}
        </h1>

        <p style="margin:0;color:#6b7280;">
            Define what users assigned to this role can access in Core.
        </p>
    </div>

    @if($errors->any())
        <div style="padding:12px 14px;margin-bottom:16px;border-radius:8px;background:#fef2f2;color:#991b1b;">
            @foreach($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <form
        method="POST"
        action="{{ $editing ? '/admin/users/roles/'.$role->id : '/admin/users/roles' }}"
    >
        @csrf

        @if($editing)
            @method('PUT')
        @endif

        <div style="background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:20px;margin-bottom:18px;">
            <div style="margin-bottom:18px;">
                <label style="display:block;font-weight:600;color:#111827;margin-bottom:6px;">
                    Role Name
                </label>

                @if($isSystem)
                    <input
                        type="text"
                        value="{{ $role->name }}"
                        disabled
                        style="width:100%;box-sizing:border-box;padding:10px 12px;border:1px solid #d1d5db;border-radius:8px;background:#f9fafb;color:#6b7280;"
                    >

                    <div style="font-size:12px;color:#6b7280;margin-top:6px;">
                        Core system role names cannot be changed.
                    </div>
                @else
                    <input
                        type="text"
                        name="name"
                        value="{{ old('name', $role->name ?? '') }}"
                        required
                        maxlength="100"
                        style="width:100%;box-sizing:border-box;padding:10px 12px;border:1px solid #d1d5db;border-radius:8px;"
                    >
                @endif
            </div>

            <div>
                <label style="display:block;font-weight:600;color:#111827;margin-bottom:6px;">
                    Description
                </label>

                <textarea
                    name="description"
                    rows="3"
                    maxlength="1000"
                    style="width:100%;box-sizing:border-box;padding:10px 12px;border:1px solid #d1d5db;border-radius:8px;"
                >{{ old('description', $role->description ?? '') }}</textarea>
            </div>
        </div>

        <div style="background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:20px;margin-bottom:18px;">
            <div style="margin-bottom:16px;">

<div class="mb-4">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <div>
            <label class="form-label fw-semibold mb-1">
                Permissions
            </label>
            <div class="text-muted small">
                Select exactly what this role can see and do.
            </div>
        </div>
    </div>

    @php
        $selectedPermissionIds = collect(
            old(
                'permissions',
                isset($role)
                    ? $rolePermissions ?? []
                    : []
            )
        )->map(fn ($id) => (string) $id)->all();

        $permissionsByKey = collect($permissions ?? [])
            ->keyBy('key');
    @endphp

    <div class="row g-3">
        @foreach($permissionGroups as $groupKey => $group)
            <div class="col-12 col-lg-6">
                <div class="border rounded p-3">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div>
                            <div class="fw-semibold">
                                {{ $group['label'] ?? $groupKey }}
                            </div>

                            @if(!empty($group['source_type']))
                                <div class="small text-muted">
                                    {{ ucfirst($group['source_type']) }}
                                </div>
                            @endif
                        </div>

                        <button
                            type="button"
                            class="btn btn-sm btn-outline-secondary js-permission-group-toggle"
                            data-group="{{ $groupKey }}"
                        >
                            Select all
                        </button>
                    </div>

                    <div class="row g-2">
                        @foreach(($group['actions'] ?? []) as $action => $actionLabel)
                            @php
                                $permissionKey = $groupKey . '.' . $action;
                                $permission = $permissionsByKey->get($permissionKey);
                            @endphp

                            @if($permission)
                                <div class="col-12 col-md-6 col-xl-4">
                                    <label class="border rounded p-3 d-flex gap-2 h-100 w-100">
                                        <input
                                            type="checkbox"
                                            class="form-check-input mt-1 js-permission-checkbox"
                                            data-group="{{ $groupKey }}"
                                            name="permissions[]"
                                            value="{{ $permission->id }}"
                                            @checked(
                                                in_array(
                                                    (string) $permission->id,
                                                    $selectedPermissionIds,
                                                    true
                                                )
                                            )
                                        >

                                        <span>
                                            <span class="d-block fw-semibold">
                                                {{ $actionLabel }}
                                            </span>

                                            <span class="d-block small text-muted">
                                                {{ $permissionKey }}
                                            </span>
                                        </span>
                                    </label>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>

<script>
document.addEventListener('click', function (event) {
    const button = event.target.closest('.js-permission-group-toggle');

    if (!button) {
        return;
    }

    const group = button.dataset.group;

    const checkboxes = Array.from(
        document.querySelectorAll(
            '.js-permission-checkbox[data-group="' + group + '"]'
        )
    );

    if (!checkboxes.length) {
        return;
    }

    const shouldCheck = checkboxes.some(function (checkbox) {
        return !checkbox.checked;
    });

    checkboxes.forEach(function (checkbox) {
        checkbox.checked = shouldCheck;
    });

    button.textContent = shouldCheck
        ? 'Clear all'
        : 'Select all';
});
</script>
<button
                type="submit"
                style="padding:10px 18px;border:0;border-radius:8px;background:#111827;color:#fff;font-weight:600;cursor:pointer;"
            >
                {{ $editing ? 'Save Role' : 'Create Role' }}
            </button>
        </div>
    </form>
</div>
@endsection
