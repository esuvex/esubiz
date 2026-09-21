@extends('tenant.admin.layouts.app')

@section('content')
<div class="mx-auto max-w-7xl space-y-6">

    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            {{-- ESUBIZ_BASIC_FORM_BUILDER_PAGE_IDENTITY_V1 --}}
            <h1 class="text-2xl font-black text-slate-900">
                Basic Form Builder
            </h1>
            <p class="mt-1 text-sm text-slate-500">
                Create and manage standard website forms.
            </p>
        </div>

        <a
            href="{{ route('tenant.cms.forms.create', ['subdomain' => $website->subdomain]) }}"
            class="inline-flex items-center justify-center rounded-xl bg-slate-900 px-5 py-3 text-sm font-bold text-white hover:bg-slate-800"
        >
            + Create Form
        </a>
    </div>

    @include('tenant.admin.components.resource-measurement', [
        'resourceKey' => 'forms',
        'label' => 'Forms Usage',
    ])

    @if(session('success'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-800">
            {{ session('error') }}
        </div>
    @endif

    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">

        @if($forms->isEmpty())
            <div class="px-6 py-16 text-center">
                <div class="text-lg font-black text-slate-900">
                    No forms yet
                </div>

                <p class="mx-auto mt-2 max-w-lg text-sm text-slate-500">
                    Create your first form. Forms can later be embedded in pages
                    and used by Core features such as registration.
                </p>

                <a
                    href="{{ route('tenant.cms.forms.create', ['subdomain' => $website->subdomain]) }}"
                    class="mt-6 inline-flex rounded-xl bg-slate-900 px-5 py-3 text-sm font-bold text-white"
                >
                    Create Form
                </a>
            </div>
        @else
            <div class="divide-y divide-slate-100">
                @foreach($forms as $form)
                    <div class="flex flex-col gap-4 px-6 py-5 sm:flex-row sm:items-center sm:justify-between">
                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <h2 class="font-black text-slate-900">
                                    {{ $form->name }}
                                </h2>

                                @if($form->is_system)
                                    <span class="rounded-full bg-blue-50 px-2.5 py-1 text-[11px] font-black uppercase tracking-wide text-blue-700">
                                        System
                                    </span>
                                @endif

                                @if(!$form->is_active)
                                    <span class="rounded-full bg-slate-100 px-2.5 py-1 text-[11px] font-black uppercase tracking-wide text-slate-500">
                                        Inactive
                                    </span>
                                @endif
                            </div>

                            <div class="mt-1 text-sm text-slate-500">
                                {{ $form->field_count }}
                                {{ $form->field_count === 1 ? 'field' : 'fields' }}
                                · {{ $form->submission_count ?? 0 }}
                                {{ ($form->submission_count ?? 0) === 1 ? 'submission' : 'submissions' }}
                                · {{ $form->slug }}
                            </div>

                            @if($form->description)
                                <p class="mt-2 max-w-2xl text-sm text-slate-600">
                                    {{ $form->description }}
                                </p>
                            @endif
                        </div>

                        <div class="flex shrink-0 items-center gap-2">
                            {{-- ESUBIZ_CORE_FORM_SUBMISSIONS_INDEX_V1 --}}
                            @php
                                $submissionSettings =
                                    json_decode(
                                        (string) ($form->settings ?? ''),
                                        true
                                    );

                                $submissionSettings =
                                    is_array($submissionSettings)
                                        ? $submissionSettings
                                        : [];

                                $submissionPurpose =
                                    (string) (
                                        $submissionSettings['purpose']
                                        ?? ''
                                    );

                                $submissionDefault =
                                    (string) (
                                        $submissionSettings[
                                            'core_default_form'
                                        ] ?? ''
                                    );

                                $isAuthSubmissionForm =
                                    str_starts_with(
                                        $submissionPurpose,
                                        'authentication.'
                                    )
                                    || in_array(
                                        $submissionDefault,
                                        [
                                            'registration',
                                            'login',
                                            'password-reset',
                                            'password_reset',
                                        ],
                                        true
                                    );
                            @endphp

                            @unless($isAuthSubmissionForm)
                                <a
                                    href="{{ route('tenant.cms.forms.submissions.index', ['subdomain' => $website->subdomain, 'form' => $form->id]) }}"
                                    class="rounded-lg border border-slate-200 px-4 py-2 text-sm font-bold text-slate-700 hover:bg-slate-50"
                                >
                                    Submissions
                                    @if(($form->submission_count ?? 0) > 0)
                                        ({{ $form->submission_count }})
                                    @endif
                                </a>
                            @endunless

                            <a
                                href="{{ route('tenant.cms.forms.edit', ['subdomain' => $website->subdomain, 'form' => $form->id]) }}"
                                class="rounded-lg border border-slate-200 px-4 py-2 text-sm font-bold text-slate-700 hover:bg-slate-50"
                            >
                                Edit
                            </a>

                            @unless($form->is_system)
                                <form
                                    method="POST"
                                    action="{{ route('tenant.cms.forms.destroy', ['subdomain' => $website->subdomain, 'form' => $form->id]) }}"
                                    onsubmit="return confirm('Delete this form?');"
                                >
                                    @csrf
                                    @method('DELETE')

                                    <button
                                        type="submit"
                                        class="rounded-lg border border-red-200 px-4 py-2 text-sm font-bold text-red-700 hover:bg-red-50"
                                    >
                                        Delete
                                    </button>
                                </form>
                            @endunless
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

    </div>
</div>
@endsection
