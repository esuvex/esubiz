@extends('tenant.admin.layouts.app')

@section('content')
<div class="mx-auto max-w-7xl space-y-6">

    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <a
                href="{{ route('tenant.cms.forms.index', ['subdomain' => $website->subdomain]) }}"
                class="text-sm font-bold text-slate-500 hover:text-slate-900"
            >
                ← Forms
            </a>

            <h1 class="mt-2 text-2xl font-black text-slate-900">
                {{ $formRecord->name }} Submissions
            </h1>

            <p class="mt-1 text-sm text-slate-500">
                Review entries submitted through this form.
            </p>
        </div>
    </div>

    @if(session('success'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800">
            {{ session('success') }}
        </div>
    @endif

    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">

        @if($submissions->isEmpty())
            <div class="px-6 py-16 text-center">
                <div class="text-lg font-black text-slate-900">
                    No submissions yet
                </div>

                <p class="mt-2 text-sm text-slate-500">
                    New entries submitted through this form will appear here.
                </p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-black uppercase tracking-wide text-slate-500">
                                Status
                            </th>

                            <th class="px-6 py-3 text-left text-xs font-black uppercase tracking-wide text-slate-500">
                                Submission
                            </th>

                            <th class="px-6 py-3 text-left text-xs font-black uppercase tracking-wide text-slate-500">
                                Date
                            </th>

                            <th class="px-6 py-3 text-right text-xs font-black uppercase tracking-wide text-slate-500">
                                Action
                            </th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100 bg-white">
                        @foreach($submissions as $submission)
                            @php
                                $rowData = json_decode(
                                    (string) ($submission->data ?? ''),
                                    true
                                );

                                $rowData = is_array($rowData)
                                    ? $rowData
                                    : [];

                                $preview = collect($fields)
                                    ->map(function ($field) use ($rowData) {
                                        $value =
                                            $rowData[$field->name]
                                            ?? null;

                                        if (is_array($value)) {
                                            $value = implode(', ', $value);
                                        }

                                        $value = trim(
                                            (string) $value
                                        );

                                        if ($value === '') {
                                            return null;
                                        }

                                        return
                                            $field->label
                                            . ': '
                                            . $value;
                                    })
                                    ->filter()
                                    ->take(2)
                                    ->implode(' · ');
                            @endphp

                            <tr>
                                <td class="whitespace-nowrap px-6 py-4">
                                    @if(strtolower((string) $submission->status) === 'new')
                                        <span class="rounded-full bg-blue-50 px-2.5 py-1 text-xs font-black text-blue-700">
                                            New
                                        </span>
                                    @else
                                        <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-black text-slate-600">
                                            Read
                                        </span>
                                    @endif
                                </td>

                                <td class="px-6 py-4">
                                    <div class="font-bold text-slate-900">
                                        Submission #{{ $submission->id }}
                                    </div>

                                    @if($preview)
                                        <div class="mt-1 max-w-2xl truncate text-sm text-slate-500">
                                            {{ $preview }}
                                        </div>
                                    @endif
                                </td>

                                <td class="whitespace-nowrap px-6 py-4 text-sm text-slate-500">
                                    {{ \Illuminate\Support\Carbon::parse($submission->created_at)->format('M j, Y g:i A') }}
                                </td>

                                <td class="whitespace-nowrap px-6 py-4 text-right">
                                    <a
                                        href="{{ route('tenant.cms.forms.submissions.show', ['subdomain' => $website->subdomain, 'form' => $formRecord->id, 'submission' => $submission->id]) }}"
                                        class="inline-flex rounded-lg border border-slate-200 px-4 py-2 text-sm font-bold text-slate-700 hover:bg-slate-50"
                                    >
                                        View
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($submissions->hasPages())
                <div class="border-t border-slate-100 px-6 py-4">
                    {{ $submissions->links() }}
                </div>
            @endif
        @endif
    </div>
</div>
@endsection
