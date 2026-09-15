{{-- ESUBIZ_CORE_EMAIL_MESSAGE_ROWS_V1 --}}

@if($messages->isEmpty())

    <div class="py-12 text-center">
        <div class="text-sm font-semibold text-slate-700">
            No emails found
        </div>

        <div class="mt-1 text-sm text-slate-500">
            This folder does not contain any matching emails.
        </div>
    </div>

@else

    <div class="divide-y divide-slate-100">

        @foreach($messages as $message)

            @php
                $counterparty =
                    ($message->direction ?? 'inbound')
                        === 'outbound'
                            ? $message->to_addresses
                            : $message->from_address;

                $source =
                    $message->source_label
                        ?? 'Direct Email';
            @endphp

            <button
                type="button"
                data-message-id="{{ $message->id }}"
                class="core-email-message-row flex w-full gap-4 px-2 py-4 text-left transition hover:bg-slate-50"
            >

                <div class="min-w-0 flex-1">

                    <div class="flex flex-wrap items-center gap-2">

                        <span class="truncate text-sm font-bold text-slate-900">
                            {{ $counterparty }}
                        </span>

                        <span class="rounded-full bg-slate-100 px-2 py-1 text-[11px] font-bold text-slate-600">
                            {{ $source }}
                        </span>

                        @if($message->has_attachments ?? false)
                            <span
                                class="text-xs text-slate-500"
                                title="Has attachment"
                            >
                                📎
                            </span>
                        @endif

                    </div>

                    <div class="mt-1 truncate text-sm font-semibold text-slate-700">
                        {{ $message->subject ?: '(No subject)' }}
                    </div>

                    <div class="mt-1 truncate text-sm text-slate-500">
                        {{ strip_tags($message->body ?? '') }}
                    </div>

                </div>

                <div class="shrink-0 text-xs font-medium text-slate-400">
                    {{ isset($message->created_at)
                        ? \Illuminate\Support\Carbon::parse(
                            $message->created_at
                        )->format('M j, H:i')
                        : '' }}
                </div>

            </button>

        @endforeach

    </div>

@endif
