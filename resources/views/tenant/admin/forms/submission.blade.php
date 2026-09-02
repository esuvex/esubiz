@extends('tenant.admin.layouts.app')

@section('content')
<div class="mx-auto max-w-4xl space-y-6">

    <div>
        <a
            href="{{ route('tenant.cms.forms.submissions.index', ['subdomain' => $website->subdomain, 'form' => $formRecord->id]) }}"
            class="text-sm font-bold text-slate-500 hover:text-slate-900"
        >
            ← {{ $formRecord->name }} Submissions
        </a>

        <div class="mt-3 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-black text-slate-900">
                    Submission #{{ $submissionRecord->id }}
                </h1>

                <p class="mt-1 text-sm text-slate-500">
                    Submitted
                    {{ \Illuminate\Support\Carbon::parse($submissionRecord->created_at)->format('M j, Y g:i A') }}
                </p>
            </div>

            <span class="self-start rounded-full bg-slate-100 px-3 py-1.5 text-xs font-black uppercase tracking-wide text-slate-600">
                {{ $submissionRecord->status }}
            </span>
        </div>
    </div>

    @if(session('success'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800">
            {{ session('success') }}
        </div>
    @endif

    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="divide-y divide-slate-100">

            @foreach($fields as $field)
                @php
                    $value =
                        $submissionData[$field->name]
                        ?? null;

                    if (is_array($value)) {
                        $value = implode(', ', $value);
                    }
                @endphp

                <div class="grid gap-2 px-6 py-5 sm:grid-cols-[180px_1fr]">
                    <div class="text-sm font-black text-slate-700">
                        {{ $field->label }}
                    </div>

                    <div class="whitespace-pre-wrap break-words text-sm text-slate-900">{{ ($value !== null && $value !== '') ? $value : '—' }}</div>
                </div>
            @endforeach

            @foreach($submissionData as $key => $value)
                @continue($fields->contains('name', $key))

                @php
                    if (is_array($value)) {
                        $value = implode(', ', $value);
                    }
                @endphp

                <div class="grid gap-2 px-6 py-5 sm:grid-cols-[180px_1fr]">
                    <div class="text-sm font-black text-slate-700">
                        {{ \Illuminate\Support\Str::headline($key) }}
                    </div>

                    <div class="whitespace-pre-wrap break-words text-sm text-slate-900">{{ ($value !== null && $value !== '') ? $value : '—' }}</div>
                </div>
            @endforeach

        </div>
    </div>

    <div class="flex flex-wrap gap-3">

        <form
            method="POST"
            action="{{ route('tenant.cms.forms.submissions.status', ['subdomain' => $website->subdomain, 'form' => $formRecord->id, 'submission' => $submissionRecord->id]) }}"
        >
            @csrf
            @method('PATCH')

            <input
                type="hidden"
                name="status"
                value="{{ strtolower((string) $submissionRecord->status) === 'new' ? 'read' : 'new' }}"
            >

            <button
                type="submit"
                class="rounded-xl border border-slate-200 bg-white px-5 py-3 text-sm font-bold text-slate-700 hover:bg-slate-50"
            >
                {{ strtolower((string) $submissionRecord->status) === 'new' ? 'Mark Read' : 'Mark Unread' }}
            </button>
        </form>

        <form
            method="POST"
            action="{{ route('tenant.cms.forms.submissions.destroy', ['subdomain' => $website->subdomain, 'form' => $formRecord->id, 'submission' => $submissionRecord->id]) }}"
            onsubmit="return confirm('Delete this submission permanently?');"
        >
            @csrf
            @method('DELETE')

            <button
                type="submit"
                class="rounded-xl border border-red-200 bg-white px-5 py-3 text-sm font-bold text-red-700 hover:bg-red-50"
            >
                Delete Submission
            </button>
        </form>

    </div>
</div>
@endsection
