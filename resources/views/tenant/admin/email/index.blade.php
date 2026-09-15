@extends('tenant.admin.layouts.app')

@section('title', 'Email')

@section('content')

{{-- ESUBIZ_CORE_GENERAL_EMAIL_PAGE_V1 --}}

<div
    id="core-email-workspace"
    class="mx-auto max-w-7xl"
    data-messages-url="{{ route('tenant.cms.email.messages', ['subdomain' => request()->route('subdomain')]) }}"
    data-save-draft-url="{{ route('tenant.cms.email.drafts.store', ['subdomain' => request()->route('subdomain')]) }}"
    data-remove-draft-attachment-url-template="{{ route('tenant.cms.email.drafts.attachments.destroy', ['subdomain' => request()->route('subdomain'), 'message' => 0, 'attachment' => 0]) }}"
    data-message-url-template="{{ route('tenant.cms.email.message', ['subdomain' => request()->route('subdomain'), 'message' => 0]) }}"
    data-premium-url="{{ route('tenant.cms.email.premium-data', ['subdomain' => request()->route('subdomain')]) }}"
    data-sender-mode-url="{{ route('tenant.cms.email.sender-mode', ['subdomain' => request()->route('subdomain')]) }}"
>
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-900">
            Email
        </h1>

        <p class="mt-1 text-sm text-slate-500">
            Send, receive and manage email across your Core website.
        </p>
    </div>

    {{-- ESUBIZ_CORE_EMAIL_SENDER_SELECTOR_V3 --}}
{{-- ESUBIZ_CORE_EMAIL_SENDER_LABEL_UI_V4 --}}
    @php
        $defaultEmailService =
            $emailServiceData['services']['default'] ?? null;

        $premiumEmailService =
            $emailServiceData['services']['premium'] ?? null;

        $premiumSelected =
            $emailSenderTransport === 'premium_smtp';
    @endphp

    <div class="mb-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h2 class="text-base font-bold text-slate-900">
                    Email Sender
                </h2>

                <p class="mt-1 text-sm text-slate-500">
                    Choose how this website sends Core emails.
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <button
                    type="button"
                    data-email-info="default"
                    aria-label="Default email sender information"
                    title="Default email sender information"
                    class="inline-flex h-6 w-6 items-center justify-center rounded-full border border-slate-300 text-xs font-bold text-slate-500 transition hover:bg-slate-100 hover:text-slate-700"
                >
                    i
                </button>

                <span
                    id="core-email-default-label"
                    class="text-sm font-semibold {{ $premiumSelected ? 'text-slate-400' : 'text-slate-900' }}"
                >
                    Default
                </span>

                <button
                    id="core-email-sender-toggle"
                    type="button"
                    role="switch"
                    aria-checked="{{ $premiumSelected ? 'true' : 'false' }}"
                    class="relative inline-flex h-7 w-14 shrink-0 rounded-full transition
                        {{ $premiumSelected ? 'bg-blue-600' : 'bg-slate-300' }}"
                >
                    <span
                        id="core-email-sender-knob"
                        class="absolute top-1 h-5 w-5 rounded-full bg-white shadow transition-all
                            {{ $premiumSelected ? 'left-8' : 'left-1' }}"
                    ></span>
                </button>

                <span
                    id="core-email-premium-label"
                    class="text-sm font-semibold {{ $premiumSelected ? 'text-blue-600' : 'text-slate-400' }}"
                >
                    Premium
                </span>

                <button
                    type="button"
                    data-email-info="premium"
                    aria-label="Premium email sender information"
                    title="Premium email sender information"
                    class="inline-flex h-6 w-6 items-center justify-center rounded-full border border-blue-300 text-xs font-bold text-blue-600 transition hover:bg-blue-50"
                >
                    i
                </button>
            </div>
        </div>

        <div
            id="core-email-premium-panel"
            class="{{ $premiumSelected ? '' : 'hidden' }} mt-5 border-t border-slate-100 pt-5"
        >
            <div class="grid gap-4 lg:grid-cols-3">
                <div class="rounded-xl bg-blue-50 p-4">
                    <p class="text-xs font-semibold uppercase tracking-wide text-blue-600">
                        Email Credit Balance
                    </p>

                    <p
                        id="core-email-credit-balance"
                        class="mt-2 text-2xl font-bold text-slate-900"
                    >
                        {{ number_format((float) ($emailServiceData['premium']['balance'] ?? 0), 0) }}
                    </p>
                </div>

                <div class="lg:col-span-2">
                    <h3 class="text-sm font-bold text-slate-900">
                        Purchase Email Credits
                    </h3>

                    <div
                        id="core-email-credit-packages"
                        class="mt-3 grid gap-3 sm:grid-cols-2 xl:grid-cols-3"
                    >
                        @forelse(($emailServiceData['premium']['packages'] ?? []) as $package)
                            <div class="rounded-xl border border-slate-200 p-4">
                                <p class="font-bold text-slate-900">
                                    {{ $package['name'] }}
                                </p>

                                <p class="mt-1 text-sm text-slate-500">
                                    {{ number_format($package['credits']) }} Email Credits
                                </p>

                                <p class="mt-2 font-semibold text-slate-900">
                                    {{ $package['currency'] }}
                                    {{ number_format($package['price'], 2) }}
                                </p>

                                <button
                                    type="button"
                                    data-email-package="{{ $package['id'] }}"
                                    class="mt-3 w-full rounded-lg bg-blue-600 px-3 py-2 text-sm font-semibold text-white hover:bg-blue-700"
                                >
                                    Purchase
                                </button>
                            </div>
                        @empty
                            <div class="text-sm text-slate-500">
                                No Email Credit package is currently available.
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="mt-6">
                <h3 class="text-sm font-bold text-slate-900">
                    Email Credit Logs
                </h3>

                <div class="mt-3 overflow-x-auto rounded-xl border border-slate-200">
                    <table class="min-w-full text-left text-sm">
                        <thead class="bg-slate-50 text-xs uppercase text-slate-500">
                            <tr>
                                <th class="px-4 py-3">Date</th>
                                <th class="px-4 py-3">Type</th>
                                <th class="px-4 py-3">Credits</th>
                                <th class="px-4 py-3">Balance</th>
                            </tr>
                        </thead>

                        <tbody
                            id="core-email-credit-log"
                            class="divide-y divide-slate-100"
                        ></tbody>
                    </table>
                </div>

                <div class="mt-3 flex items-center justify-between">
                    <button
                        id="core-email-credit-previous"
                        type="button"
                        class="rounded-lg border border-slate-300 px-3 py-2 text-sm font-semibold disabled:opacity-40"
                    >
                        Previous
                    </button>

                    <span
                        id="core-email-credit-page"
                        class="text-sm text-slate-500"
                    >
                        Page 1
                    </span>

                    <button
                        id="core-email-credit-next"
                        type="button"
                        class="rounded-lg border border-slate-300 px-3 py-2 text-sm font-semibold disabled:opacity-40"
                    >
                        Next
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div
        id="core-email-info-modal"
        class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-950/50 p-4"
    >
        <div class="w-full max-w-lg rounded-2xl bg-white p-6 shadow-xl">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h3
                        id="core-email-info-title"
                        class="text-lg font-bold text-slate-900"
                    ></h3>

                    <p
                        id="core-email-info-description"
                        class="mt-2 text-sm text-slate-500"
                    ></p>
                </div>

                <button
                    id="core-email-info-close"
                    type="button"
                    class="rounded-lg px-3 py-1 text-xl text-slate-400 hover:bg-slate-100"
                >
                    &times;
                </button>
            </div>

            <div
                id="core-email-info-content"
                class="mt-5 whitespace-pre-line rounded-xl bg-slate-50 p-4 text-sm leading-6 text-slate-700"
            ></div>
        </div>
    </div>

    {{-- LEVEL 1: AVAILABLE EMAIL ADDRESSES --}}
    {{-- ESUBIZ_CORE_MAIL_INTERFACE_V2 --}}
    {{-- ESUBIZ_CORE_MAIL_EMPTY_WORKSPACE_V3 --}}
    <div class="mb-4 rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="flex flex-col gap-4 border-b border-slate-200 px-5 pt-5 sm:flex-row sm:items-end sm:justify-between">
            <div class="min-w-0 flex-1 overflow-x-auto">
                @if($mailboxes->isNotEmpty())
                    <div class="flex min-w-max gap-2">
                        @foreach($mailboxes as $mailbox)
                            <button
                                type="button"
                                data-mailbox-id="{{ $mailbox->id }}"
                                data-mailbox-address="{{ $mailbox->email }}"
                                class="core-mailbox-tab rounded-t-xl px-4 py-3 text-sm font-semibold
                                    {{ (int) $selectedMailboxId === (int) $mailbox->id
                                        ? 'bg-slate-900 text-white'
                                        : 'bg-slate-50 text-slate-600 hover:bg-slate-100' }}"
                            >
                                {{ $mailbox->email }}
                            </button>
                        @endforeach
                    </div>
                @else
                    <div class="pb-4">
                        <p class="text-sm font-semibold text-slate-900">
                            Email Addresses
                        </p>

                        <p class="mt-1 text-sm text-slate-500">
                            Manage the email addresses available to this website.
                        </p>
                    </div>
                @endif
            </div>

            <button
                type="button"
                id="core-email-add-address"
                class="mb-4 inline-flex shrink-0 items-center justify-center gap-2 rounded-lg bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-800"
            >
                <span class="text-lg leading-none">+</span>
                <span>Add Email Address</span>
            </button>
        </div>

        {{-- ESUBIZ_CORE_MAIL_EMPTY_HERO_REMOVED_V5 --}}
        {{-- Folder tabs are the canonical zero-mailbox state. --}}
    </div>

    {{-- ESUBIZ_CORE_MAIL_ZERO_MAILBOX_FOLDERS_V4 --}}
    {{-- The Email workspace remains visible even before the first mailbox exists. --}}
    {{-- Mailbox-dependent actions render their own empty/disabled state. --}}

        {{-- LEVEL 2: SELECTED MAILBOX --}}
        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">

            <div class="overflow-x-auto border-b border-slate-200 px-4">
                <div class="flex min-w-max gap-1 py-3">

                    @foreach([
                        'compose' => 'Create Email',
                        'inbox' => 'Inbox',
                        'spam' => 'Spam',
                        'draft' => 'Draft',
                        'sent' => 'Sent',
                        'trash' => 'Trash',
                        'settings' => 'Settings',
                        'branding' => 'Branding',
                    ] as $key => $label)

                        <button
                            type="button"
                            data-email-section="{{ $key }}"
                            class="core-email-section rounded-lg px-4 py-2 text-sm font-semibold
                                {{ $key === 'inbox'
                                    ? 'bg-slate-900 text-white'
                                    : 'text-slate-600 hover:bg-slate-100' }}"
                        >
                            {{ $label }}
                        </button>

                    @endforeach

                </div>
            </div>

            <div class="p-5">

                <div id="core-email-list-panel">

                    <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">

                        <div>
                            <h2
                                id="core-email-folder-title"
                                class="text-lg font-bold text-slate-900"
                            >
                                Inbox
                            </h2>

                            <p
                                id="core-email-active-address"
                                class="mt-1 text-sm text-slate-500"
                            ></p>
                        </div>

                        {{-- ESUBIZ_CORE_MAIL_FOLDER_AJAX_SEARCH_V3 --}}
                        <div class="relative w-full sm:max-w-sm">
                            <div class="relative">
                                <input
                                    id="core-email-search"
                                    type="search"
                                    autocomplete="off"
                                    placeholder="Search this folder..."
                                    class="w-full rounded-xl border border-slate-300 px-4 py-2.5 pr-10 text-sm outline-none focus:border-slate-500"
                                >

                                <div
                                    id="core-email-search-spinner"
                                    class="pointer-events-none absolute inset-y-0 right-3 hidden items-center text-xs text-slate-400"
                                >
                                    ...
                                </div>
                            </div>

                            <div
                                id="core-email-search-dropdown"
                                class="absolute left-0 right-0 z-40 mt-2 hidden max-h-96 overflow-y-auto rounded-xl border border-slate-200 bg-white shadow-xl"
                            >
                                <div
                                    id="core-email-search-results"
                                    class="divide-y divide-slate-100"
                                ></div>
                            </div>
                        </div>

                    </div>

                    <div id="core-email-message-results">
                        <div class="py-10 text-center text-sm text-slate-500">
                            Loading emails...
                        </div>
                    </div>

                    <div class="mt-5 flex items-center justify-between border-t border-slate-100 pt-4">

                        <button
                            id="core-email-previous"
                            type="button"
                            disabled
                            class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 disabled:cursor-not-allowed disabled:opacity-40"
                        >
                            Previous
                        </button>

                        <span
                            id="core-email-page-label"
                            class="text-sm font-medium text-slate-500"
                        >
                            Page 1
                        </span>

                        <button
                            id="core-email-next"
                            type="button"
                            disabled
                            class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 disabled:cursor-not-allowed disabled:opacity-40"
                        >
                            Next
                        </button>

                    </div>

                </div>

                {{-- ESUBIZ_CORE_EMAIL_MESSAGE_VIEW_UI_V1 --}}
                <div
                    id="core-email-message-view-panel"
                    class="hidden rounded-2xl border border-slate-200 bg-white"
                >
                    <div class="border-b border-slate-200 p-5">
                        <button
                            type="button"
                            id="core-email-message-back"
                            class="mb-4 inline-flex items-center gap-2 text-sm font-semibold text-slate-600 hover:text-slate-900"
                        >
                            <span>←</span>
                            <span>Back to folder</span>
                        </button>

                        <div class="flex flex-wrap items-start justify-between gap-4">
                            <div class="min-w-0 flex-1">
                                <h2
                                    id="core-email-view-subject"
                                    class="text-xl font-bold text-slate-900"
                                >
                                    Email
                                </h2>

                                <div class="mt-3 space-y-1 text-sm">
                                    <div>
                                        <span class="font-semibold text-slate-700">
                                            From:
                                        </span>
                                        <span
                                            id="core-email-view-from"
                                            class="text-slate-600"
                                        ></span>
                                    </div>

                                    <div>
                                        <span class="font-semibold text-slate-700">
                                            To:
                                        </span>
                                        <span
                                            id="core-email-view-to"
                                            class="text-slate-600"
                                        ></span>
                                    </div>

                                    <div
                                        id="core-email-view-cc-row"
                                        class="hidden"
                                    >
                                        <span class="font-semibold text-slate-700">
                                            CC:
                                        </span>
                                        <span
                                            id="core-email-view-cc"
                                            class="text-slate-600"
                                        ></span>
                                    </div>
                                </div>
                            </div>

                            <div class="text-right">
                                <div
                                    id="core-email-view-source"
                                    class="inline-flex rounded-full bg-slate-100 px-2.5 py-1 text-xs font-bold text-slate-600"
                                >
                                    Direct Email
                                </div>

                                <div
                                    id="core-email-view-date"
                                    class="mt-2 text-xs text-slate-400"
                                ></div>
                            </div>
                        </div>
                    </div>

                    <div class="p-5">
                        <div
                            id="core-email-view-loading"
                            class="py-16 text-center text-sm text-slate-500"
                        >
                            Loading email...
                        </div>

                        <div
                            id="core-email-view-content"
                            class="hidden"
                        >
                            <div
                                id="core-email-view-body"
                                class="prose max-w-none break-words text-sm leading-7 text-slate-700"
                            ></div>

                            <div
                                id="core-email-view-attachments-section"
                                class="mt-8 hidden border-t border-slate-200 pt-5"
                            >
                                <h3 class="text-sm font-bold text-slate-900">
                                    Attachments
                                </h3>

                                <div
                                    id="core-email-view-attachments"
                                    class="mt-3 grid gap-3 sm:grid-cols-2 lg:grid-cols-3"
                                ></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div
                    id="core-email-compose-panel"
                    class="hidden"
                >
                    {{-- ESUBIZ_CORE_MAIL_COMPOSE_UI_V2 --}}
                    <div class="mb-5">
                        <h2 class="text-lg font-bold text-slate-900">
                            Create Email
                        </h2>

                        <p class="mt-1 text-sm text-slate-500">
                            Sending from
                            <span
                                id="core-email-compose-address"
                                class="font-semibold text-slate-700"
                            ></span>
                        </p>
                    </div>

                    <div class="space-y-4">
                        <div>
                            <label class="mb-1.5 block text-sm font-semibold text-slate-700">
                                To
                            </label>
                            <input
                                id="core-email-compose-to"
                                type="text"
                                placeholder="recipient@example.com"
                                class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm outline-none focus:border-slate-500"
                            >
                        </div>

                        <div class="grid gap-4 md:grid-cols-2">
                            <div>
                                <label class="mb-1.5 block text-sm font-semibold text-slate-700">
                                    CC
                                </label>
                                <input
                                    id="core-email-compose-cc"
                                    type="text"
                                    placeholder="Optional"
                                    class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm outline-none focus:border-slate-500"
                                >
                            </div>

                            <div>
                                <label class="mb-1.5 block text-sm font-semibold text-slate-700">
                                    BCC
                                </label>
                                <input
                                    id="core-email-compose-bcc"
                                    type="text"
                                    placeholder="Optional"
                                    class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm outline-none focus:border-slate-500"
                                >
                            </div>
                        </div>

                        <div>
                            <label class="mb-1.5 block text-sm font-semibold text-slate-700">
                                Subject
                            </label>
                            <input
                                id="core-email-compose-subject"
                                type="text"
                                class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm outline-none focus:border-slate-500"
                            >
                        </div>

                        <div>
                            <label class="mb-1.5 block text-sm font-semibold text-slate-700">
                                Message
                            </label>

                            <div class="overflow-hidden rounded-xl border border-slate-300">
                                <div class="flex flex-wrap gap-1 border-b border-slate-200 bg-slate-50 p-2">
                                    <button
                                        type="button"
                                        data-compose-command="bold"
                                        class="rounded px-2.5 py-1.5 text-sm font-bold text-slate-700 hover:bg-white"
                                    >B</button>

                                    <button
                                        type="button"
                                        data-compose-command="italic"
                                        class="rounded px-2.5 py-1.5 text-sm italic text-slate-700 hover:bg-white"
                                    >I</button>

                                    <button
                                        type="button"
                                        data-compose-command="underline"
                                        class="rounded px-2.5 py-1.5 text-sm underline text-slate-700 hover:bg-white"
                                    >U</button>

                                    <button
                                        type="button"
                                        data-compose-command="insertUnorderedList"
                                        class="rounded px-2.5 py-1.5 text-sm text-slate-700 hover:bg-white"
                                    >
                                        List
                                    </button>
                                </div>

                                <div
                                    id="core-email-compose-body"
                                    contenteditable="true"
                                    class="min-h-[240px] bg-white p-4 text-sm leading-6 text-slate-800 outline-none"
                                ></div>
                            </div>
                        </div>

                        <div>
                            <label class="mb-1.5 block text-sm font-semibold text-slate-700">
                                Attachments
                            </label>

                            <input
                                id="core-email-compose-attachments"
                                type="file"
                                multiple
                                class="block w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm text-slate-600"
                            >

                            <div
                                id="core-email-compose-existing-attachments"
                                class="mt-3 hidden space-y-2"
                            ></div>
                        </div>

                        {{-- ESUBIZ_CORE_EMAIL_COMPOSE_STATUS_V1 --}}
                        <div
                            id="core-email-compose-status"
                            class="hidden rounded-xl px-4 py-3 text-sm font-medium"
                        ></div>

                        <div class="flex flex-wrap gap-3 pt-2">
                            <button
                                id="core-email-send"
                                type="button"
                                class="rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-700"
                            >
                                Send
                            </button>

                            <button
                                id="core-email-save-draft"
                                type="button"
                                class="rounded-lg border border-slate-300 px-5 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
                            >
                                Save Draft
                            </button>
                        </div>
                    </div>
                </div>

                <div
                    id="core-email-settings-panel"
                    class="hidden"
                >
                    {{-- ESUBIZ_CORE_MAIL_SETTINGS_UI_V2 --}}
                    <div class="mb-6">
                        <h2 class="text-lg font-bold text-slate-900">
                            Mailbox Settings
                        </h2>

                        <p class="mt-1 text-sm text-slate-500">
                            Settings for
                            <span
                                id="core-email-settings-address"
                                class="font-semibold text-slate-700"
                            ></span>
                        </p>
                    </div>

                    <div class="space-y-6">
                        <section class="rounded-xl border border-slate-200 p-5">
                            <h3 class="font-bold text-slate-900">
                                Email Account
                            </h3>

                            <div class="mt-4 grid gap-4 md:grid-cols-2">
                                <div>
                                    <label class="mb-1.5 block text-sm font-semibold text-slate-700">
                                        Email Address
                                    </label>

                                    <input
                                        id="core-email-settings-email"
                                        type="text"
                                        readonly
                                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-600"
                                    >
                                </div>

                                <div>
                                    <label class="mb-1.5 block text-sm font-semibold text-slate-700">
                                        Change Password
                                    </label>

                                    <input
                                        id="core-email-settings-password"
                                        type="password"
                                        autocomplete="new-password"
                                        placeholder="Enter new mailbox password"
                                        class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm outline-none focus:border-slate-500"
                                    >
                                </div>
                            </div>
                        </section>

                        <section class="rounded-xl border border-slate-200 p-5">
                            <h3 class="font-bold text-slate-900">
                                Forward Email
                            </h3>

                            <p class="mt-1 text-sm text-slate-500">
                                Forward incoming messages from this mailbox to another email address.
                            </p>

                            <input
                                id="core-email-forward-address"
                                type="email"
                                placeholder="example@gmail.com"
                                class="mt-4 w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm outline-none focus:border-slate-500"
                            >
                        </section>

                        <section class="rounded-xl border border-slate-200 p-5">
                            <h3 class="font-bold text-slate-900">
                                Automatic Email Deletion
                            </h3>

                            <p class="mt-1 text-sm text-slate-500">
                                Permanently remove messages after the selected period to free mailbox storage. Never keeps them until manually deleted.
                            </p>

                            <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
                                @foreach([
                                    'inbox' => 'Inbox',
                                    'spam' => 'Spam',
                                    'draft' => 'Draft',
                                    'sent' => 'Sent',
                                    'trash' => 'Trash',
                                ] as $folderKey => $folderLabel)
                                    <div>
                                        <label class="mb-1.5 block text-sm font-semibold text-slate-700">
                                            {{ $folderLabel }}
                                        </label>

                                        <select
                                            data-retention-folder="{{ $folderKey }}"
                                            class="w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm"
                                        >
                                            <option value="0">Never</option>
                                            <option value="7">7 days</option>
                                            <option value="14">14 days</option>
                                            <option value="30">30 days</option>
                                            <option value="60">60 days</option>
                                            <option value="90">90 days</option>
                                            <option value="180">180 days</option>
                                            <option value="365">1 year</option>
                                        </select>
                                    </div>
                                @endforeach
                            </div>
                        </section>

                        <section class="rounded-xl border border-slate-200 p-5">
                            <h3 class="font-bold text-slate-900">
                                Email Sources
                            </h3>

                            <p class="mt-1 text-sm text-slate-500">
                                Choose which Core email sources are saved and displayed in this mailbox. All sources are enabled by default.
                            </p>

                            <div class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                                @foreach([
                                    'direct_email' => 'Direct Email',
                                    'core_system' => 'Core System',
                                    'system_auth' => 'System Authentication',
                                    'crm' => 'CRM',
                                    'hr' => 'HR',
                                    'tickets' => 'Tickets',
                                    'live_chat' => 'Live Chat',
                                    'contact_form' => 'Contact Form',
                                    'marketing' => 'Marketing',
                                    'module' => 'Modules',
                                    'esubiz' => 'Esubiz',
                                ] as $sourceKey => $sourceLabel)
                                    <label class="flex items-center justify-between gap-3 rounded-xl border border-slate-200 px-4 py-3">
                                        <span class="text-sm font-semibold text-slate-700">
                                            {{ $sourceLabel }}
                                        </span>

                                        <input
                                            type="checkbox"
                                            data-email-source="{{ $sourceKey }}"
                                            checked
                                            class="h-4 w-4 rounded border-slate-300"
                                        >
                                    </label>
                                @endforeach
                            </div>
                        </section>

                        <button
                            id="core-email-save-settings"
                            type="button"
                            class="rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-200"
                        >
                            Save Settings
                        </button>
                    </div>
                </div>

            </div>

        </div>


                {{-- ESUBIZ_CORE_EMAIL_BRANDING_TENANT_LOGO_V9 --}}
                @php
                    /*
                     * Use the same Core-local website identity that belongs
                     * to this website. No Central logo copy is created.
                     */
                    $coreEmailWebsiteLogoPath =
                        data_get(
                            $coreEmailSiteSettings ?? [],
                            'theme.corporate.footer_logo_path'
                        )
                        ?? data_get(
                            $coreEmailSiteSettings ?? [],
                            'footer_logo_path'
                        )
                        ?? data_get(
                            $coreEmailSiteSettings ?? [],
                            'site_logo_path'
                        )
                        ?? data_get(
                            $coreEmailSiteSettings ?? [],
                            'theme.corporate.logo_path'
                        )
                        ?? null;

                    $coreEmailWebsiteLogoUrl = null;

                    if (
                        is_string($coreEmailWebsiteLogoPath)
                        && trim($coreEmailWebsiteLogoPath) !== ''
                    ) {
                        $coreEmailWebsiteLogoUrl =
                            request()->getSchemeAndHttpHost()
                            . '/media/'
                            . implode(
                                '/',
                                array_map(
                                    'rawurlencode',
                                    explode(
                                        '/',
                                        ltrim(
                                            $coreEmailWebsiteLogoPath,
                                            '/'
                                        )
                                    )
                                )
                            );
                    }

                    $coreEmailWebsiteName =
                        trim(
                            (string) (
                                data_get(
                                    $coreEmailSiteSettings ?? [],
                                    'website_name'
                                )
                                ?: (
                                    isset($website)
                                        ? ($website->name ?? '')
                                        : ''
                                )
                                ?: config('app.name', 'Website')
                            )
                        );

                    if ($coreEmailWebsiteName === '') {
                        $coreEmailWebsiteName = 'Website';
                    }
                @endphp

                {{-- ESUBIZ_CORE_EMAIL_BRANDING_PRODUCTION_UI_V3 --}}
                <div
                    id="core-email-branding-panel"
                    class="hidden"
                >
                    {{-- ESUBIZ_CORE_EMAIL_BRANDING_PREVIEW_TOP_REMOVED_V4 --}}
                    <div class="mb-6">
                        <h2 class="text-lg font-bold text-slate-900">
                            Email Branding
                        </h2>

                        <p class="mt-1 max-w-2xl text-sm leading-6 text-slate-500">
                            Create a consistent, professional look for every email sent from your website.
                        </p>
                    </div>

                    <div class="space-y-6">

                        <section class="rounded-xl border border-slate-200 p-5">
                            <h3 class="font-bold text-slate-900">
                                Logo
                            </h3>

                            <p class="mt-1 text-sm leading-6 text-slate-500">
                                Your website logo is used automatically. You can choose a different logo specifically for emails.
                            </p>

                            <div class="mt-4 grid gap-5 lg:grid-cols-2">
                                <div class="rounded-xl border border-slate-200 bg-slate-50 p-5">
                                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">
                                        Email Logo
                                    </p>

                                    <div
                                        id="core-email-branding-logo-preview"
                                        class="mt-4 flex min-h-28 items-center justify-center rounded-lg bg-white p-5"
                                    >
                                        @if($coreEmailWebsiteLogoUrl)
                                            <img
                                                src="{{ $coreEmailWebsiteLogoUrl }}"
                                                alt="{{ $coreEmailWebsiteName }} logo"
                                                class="max-h-20 max-w-[260px] object-contain"
                                            >
                                        @else
                                            <span class="text-lg font-bold tracking-wide text-slate-800">
                                                {{ $coreEmailWebsiteName }}
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <div>
                                    <label
                                        for="core-email-branding-logo"
                                        class="mb-1.5 block text-sm font-semibold text-slate-700"
                                    >
                                        Change Email Logo
                                    </label>

                                    <input
                                        id="core-email-branding-logo"
                                        type="file"
                                        accept="image/png,image/jpeg,image/webp,image/svg+xml"
                                        class="block w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm text-slate-600"
                                    >

                                    <p class="mt-2 text-xs leading-5 text-slate-500">
                                        Leave this unchanged to continue using your website logo.
                                    </p>
                                </div>
                            </div>
                        </section>

                        <section class="rounded-xl border border-slate-200 p-5">
                            <h3 class="font-bold text-slate-900">
                                Header
                            </h3>

                            <p class="mt-1 text-sm leading-6 text-slate-500">
                                Add a welcoming message that appears at the top of your emails.
                            </p>

                            <label
                                for="core-email-branding-header"
                                class="mt-4 mb-1.5 block text-sm font-semibold text-slate-700"
                            >
                                Header Text
                            </label>

                            <textarea
                                id="core-email-branding-header"
                                rows="3"
                                class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm leading-6 outline-none focus:border-slate-500"
                            >Thank you for connecting with us. We’re pleased to keep you updated.</textarea>
                        </section>

                        {{-- ESUBIZ_CORE_EMAIL_BRANDING_FOOTER_V7 --}}
                        <section class="rounded-xl border border-slate-200 p-5">
                            <h3 class="font-bold text-slate-900">
                                Footer
                            </h3>

                            <p class="mt-1 text-sm leading-6 text-slate-500">
                                Choose the information customers see at the bottom of your emails.
                            </p>

                            <div class="mt-5 space-y-6">

                                <div>
                                    <label
                                        for="core-email-branding-footer"
                                        class="mb-1.5 block text-sm font-semibold text-slate-700"
                                    >
                                        Footer Message
                                    </label>

                                    <textarea
                                        id="core-email-branding-footer"
                                        rows="3"
                                        class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm leading-6 outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100"
                                    >Thank you for choosing us. We value your trust and look forward to serving you.</textarea>
                                </div>

                                {{-- CONTACT DETAILS --}}
                                <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                                    <div class="flex flex-wrap items-start justify-between gap-3">
                                        <div>
                                            <h4 class="text-sm font-bold text-slate-900">
                                                Contact Details
                                            </h4>

                                            <p class="mt-1 text-xs leading-5 text-slate-500">
                                                Show your business contact information in the email footer.
                                            </p>
                                        </div>

                                        <label class="inline-flex cursor-pointer items-center gap-2">
                                            <input
                                                id="core-email-footer-contact-enabled"
                                                type="checkbox"
                                                checked
                                                class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500"
                                            >
                                            <span class="text-sm font-semibold text-slate-700">
                                                Show
                                            </span>
                                        </label>
                                    </div>

                                    <div
                                        id="core-email-footer-contact-fields"
                                        class="mt-4 grid gap-4 md:grid-cols-2"
                                    >
                                        <div>
                                            <label
                                                for="core-email-footer-phone"
                                                class="mb-1.5 block text-xs font-semibold text-slate-600"
                                            >
                                                Phone Number
                                            </label>

                                            <input
                                                id="core-email-footer-phone"
                                                type="text"
                                                placeholder="+234 800 000 0000"
                                                class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100"
                                            >
                                        </div>

                                        <div>
                                            <label
                                                for="core-email-footer-email"
                                                class="mb-1.5 block text-xs font-semibold text-slate-600"
                                            >
                                                Email Address
                                            </label>

                                            <input
                                                id="core-email-footer-email"
                                                type="email"
                                                placeholder="hello@example.com"
                                                class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100"
                                            >
                                        </div>

                                        <div>
                                            <label
                                                for="core-email-footer-website"
                                                class="mb-1.5 block text-xs font-semibold text-slate-600"
                                            >
                                                Website
                                            </label>

                                            <input
                                                id="core-email-footer-website"
                                                type="url"
                                                placeholder="https://example.com"
                                                class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100"
                                            >
                                        </div>

                                        <div>
                                            <label
                                                for="core-email-footer-address"
                                                class="mb-1.5 block text-xs font-semibold text-slate-600"
                                            >
                                                Business Address
                                            </label>

                                            <input
                                                id="core-email-footer-address"
                                                type="text"
                                                placeholder="Your business address"
                                                class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100"
                                            >
                                        </div>
                                    </div>
                                </div>

                                {{-- SOCIAL MEDIA --}}
                                <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                                    <div class="flex flex-wrap items-start justify-between gap-3">
                                        <div>
                                            <h4 class="text-sm font-bold text-slate-900">
                                                Social Media
                                            </h4>

                                            <p class="mt-1 text-xs leading-5 text-slate-500">
                                                Add links customers can use to connect with your business.
                                            </p>
                                        </div>

                                        <label class="inline-flex cursor-pointer items-center gap-2">
                                            <input
                                                id="core-email-footer-social-enabled"
                                                type="checkbox"
                                                checked
                                                class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500"
                                            >
                                            <span class="text-sm font-semibold text-slate-700">
                                                Show
                                            </span>
                                        </label>
                                    </div>

                                    <div
                                        id="core-email-footer-social-fields"
                                        class="mt-4 grid gap-4 md:grid-cols-2"
                                    >
                                        @foreach([
                                            'facebook' => 'Facebook',
                                            'instagram' => 'Instagram',
                                            'linkedin' => 'LinkedIn',
                                            'x' => 'X (Twitter)',
                                            'youtube' => 'YouTube',
                                            'tiktok' => 'TikTok',
                                        ] as $socialKey => $socialLabel)
                                            <div>
                                                <label
                                                    for="core-email-footer-social-{{ $socialKey }}"
                                                    class="mb-1.5 block text-xs font-semibold text-slate-600"
                                                >
                                                    {{ $socialLabel }}
                                                </label>

                                                <input
                                                    id="core-email-footer-social-{{ $socialKey }}"
                                                    type="url"
                                                    placeholder="Paste your {{ $socialLabel }} link"
                                                    class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100"
                                                >
                                            </div>
                                        @endforeach
                                    </div>
                                </div>

                                {{-- UNSUBSCRIBE --}}
                                <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                                    <div class="flex flex-wrap items-start justify-between gap-3">
                                        <div>
                                            <h4 class="text-sm font-bold text-slate-900">
                                                Unsubscribe Link
                                            </h4>

                                            <p class="mt-1 max-w-2xl text-xs leading-5 text-slate-500">
                                                Allow recipients to stop receiving emails they no longer want.
                                            </p>
                                        </div>

                                        <label class="inline-flex cursor-pointer items-center gap-2">
                                            <input
                                                id="core-email-footer-unsubscribe-enabled"
                                                type="checkbox"
                                                checked
                                                class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500"
                                            >
                                            <span class="text-sm font-semibold text-slate-700">
                                                Show
                                            </span>
                                        </label>
                                    </div>

                                    <div
                                        id="core-email-footer-unsubscribe-fields"
                                        class="mt-4"
                                    >
                                        <label
                                            for="core-email-footer-unsubscribe-text"
                                            class="mb-1.5 block text-xs font-semibold text-slate-600"
                                        >
                                            Link Text
                                        </label>

                                        <input
                                            id="core-email-footer-unsubscribe-text"
                                            type="text"
                                            value="Unsubscribe from these emails"
                                            maxlength="120"
                                            class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100"
                                        >
                                    </div>
                                </div>

                            </div>
                        </section>

                        <section class="rounded-xl border border-slate-200 p-5">
                            <h3 class="font-bold text-slate-900">
                                Signature
                            </h3>

                            <p class="mt-1 text-sm leading-6 text-slate-500">
                                Set the closing message used across your website emails.
                            </p>

                            <label
                                for="core-email-branding-signature"
                                class="mt-4 mb-1.5 block text-sm font-semibold text-slate-700"
                            >
                                Default Signature
                            </label>

                            <textarea
                                id="core-email-branding-signature"
                                rows="4"
                                class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm leading-6 outline-none focus:border-slate-500"
                            >Warm regards,
The Team</textarea>
                        </section>

                        <section class="rounded-xl border border-slate-200 p-5">
                            <h3 class="font-bold text-slate-900">
                                Email Style
                            </h3>

                            <p class="mt-1 text-sm leading-6 text-slate-500">
                                Fine-tune the appearance of your emails to complement your brand.
                            </p>

                            <div class="mt-5 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">

                                <div>
                                    <label
                                        for="core-email-branding-background"
                                        class="mb-1.5 block text-sm font-semibold text-slate-700"
                                    >
                                        Background
                                    </label>

                                    <div class="flex items-center gap-2">
                                        <input
                                            id="core-email-branding-background"
                                            type="color"
                                            value="#f8fafc"
                                            class="h-11 w-14 cursor-pointer rounded-lg border border-slate-300 bg-white p-1"
                                        >

                                        <span class="text-sm text-slate-500">
                                            Soft
                                        </span>
                                    </div>
                                </div>

                                <div>
                                    <label
                                        for="core-email-branding-accent"
                                        class="mb-1.5 block text-sm font-semibold text-slate-700"
                                    >
                                        Accent Color
                                    </label>

                                    <div class="flex items-center gap-2">
                                        <input
                                            id="core-email-branding-accent"
                                            type="color"
                                            value="#0f172a"
                                            class="h-11 w-14 cursor-pointer rounded-lg border border-slate-300 bg-white p-1"
                                        >

                                        <span class="text-sm text-slate-500">
                                            Classic
                                        </span>
                                    </div>
                                </div>

                                <div>
                                    <label
                                        for="core-email-branding-width"
                                        class="mb-1.5 block text-sm font-semibold text-slate-700"
                                    >
                                        Email Width
                                    </label>

                                    <select
                                        id="core-email-branding-width"
                                        class="w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm outline-none focus:border-slate-500"
                                    >
                                        <option value="560">Compact</option>
                                        <option value="640" selected>Standard</option>
                                        <option value="720">Wide</option>
                                    </select>
                                </div>

                                <div>
                                    <label
                                        for="core-email-branding-font"
                                        class="mb-1.5 block text-sm font-semibold text-slate-700"
                                    >
                                        Font Style
                                    </label>

                                    <select
                                        id="core-email-branding-font"
                                        class="w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm outline-none focus:border-slate-500"
                                    >
                                        <option value="system" selected>Modern</option>
                                        <option value="serif">Classic</option>
                                        <option value="clean">Clean</option>
                                    </select>
                                </div>

                            </div>
                        </section>

                        <section class="rounded-xl border border-slate-200 p-5">
                            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                                <div>
                                    <h3 class="font-bold text-slate-900">
                                        Email Layout
                                    </h3>

                                    <p class="mt-1 text-sm leading-6 text-slate-500">
                                        Your logo, header, message, signature and footer are combined automatically into one consistent email design.
                                    </p>
                                </div>

                                <button
                                    type="button"
                                    data-core-email-branding-preview
                                    class="inline-flex shrink-0 items-center justify-center rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
                                >
                                    Preview Email
                                </button>
                            </div>
                        </section>

                        <div class="flex justify-end">
                            <button
                                id="core-email-save-branding"
                                type="button"
                                class="rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-200"
                            >
                                Save Branding
                            </button>
                        </div>

                    </div>
                </div>

                {{-- ESUBIZ_CORE_EMAIL_BRANDING_PREVIEW_MODAL_V3 --}}
                <div
                    id="core-email-branding-preview-modal"
                    class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-950/60 p-4"
                    role="dialog"
                    aria-modal="true"
                    aria-labelledby="core-email-branding-preview-title"
                >
                    <div class="flex max-h-[92vh] w-full max-w-4xl flex-col overflow-hidden rounded-2xl bg-white shadow-2xl">
                        <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
                            <div>
                                <h2
                                    id="core-email-branding-preview-title"
                                    class="font-bold text-slate-900"
                                >
                                    Email Preview
                                </h2>

                                <p class="mt-0.5 text-xs text-slate-500">
                                    See how your branding will look in a typical email.
                                </p>
                            </div>

                            <button
                                id="core-email-branding-preview-close"
                                type="button"
                                class="flex h-9 w-9 items-center justify-center rounded-lg text-xl text-slate-400 transition hover:bg-slate-100 hover:text-slate-700"
                                aria-label="Close preview"
                            >
                                &times;
                            </button>
                        </div>

                        <div
                            id="core-email-branding-preview-stage"
                            class="overflow-y-auto bg-slate-100 p-5 sm:p-8"
                        >
                            <div
                                id="core-email-branding-preview-card"
                                class="mx-auto overflow-hidden rounded-2xl bg-white shadow-sm"
                                style="max-width:640px;"
                            >
                                <div class="border-b border-slate-100 px-8 py-7 text-center">
                                    <div
                                        id="core-email-branding-preview-logo"
                                        class="flex min-h-12 items-center justify-center text-xl font-bold tracking-wide text-slate-900"
                                    >
                                        @if($coreEmailWebsiteLogoUrl)
                                            <img
                                                src="{{ $coreEmailWebsiteLogoUrl }}"
                                                alt="{{ $coreEmailWebsiteName }} logo"
                                                class="mx-auto max-h-16 max-w-[220px] object-contain"
                                            >
                                        @else
                                            <span>
                                                {{ $coreEmailWebsiteName }}
                                            </span>
                                        @endif
                                    </div>

                                    <p
                                        id="core-email-branding-preview-header"
                                        class="mt-4 text-sm leading-6 text-slate-600"
                                    >
                                        Thank you for connecting with us. We’re pleased to keep you updated.
                                    </p>
                                </div>

                                <div class="px-8 py-8">
                                    <p class="text-sm leading-7 text-slate-700">
                                        Hello,
                                    </p>

                                    <p class="mt-4 text-sm leading-7 text-slate-700">
                                        We wanted to share an important update with you. Everything you need to know is included below, and you can contact us anytime if you need further assistance.
                                    </p>

                                    <div class="my-6 rounded-xl bg-slate-50 p-5">
                                        <p class="text-sm font-semibold text-slate-900">
                                            Your update is ready
                                        </p>

                                        <p class="mt-2 text-sm leading-6 text-slate-600">
                                            This area represents the message being sent from your website, such as an invoice, account notification, support response, booking update or other communication.
                                        </p>
                                    </div>

                                    <p
                                        id="core-email-branding-preview-signature"
                                        class="whitespace-pre-line text-sm leading-7 text-slate-700"
                                    >Warm regards,
The Team</p>
                                </div>

                                <div class="border-t border-slate-100 bg-slate-50 px-8 py-6 text-center">
                                    <p
                                        id="core-email-branding-preview-footer"
                                        class="text-xs leading-5 text-slate-500"
                                    >
                                        You’re receiving this email because you contacted us, have an account with us, or use one of our services. Please keep this email for your records.
                                    </p>

                                    <div
                                        id="core-email-branding-preview-contact"
                                        class="mx-auto mt-4 max-w-xl text-center text-xs leading-6 text-slate-500"
                                    ></div>

                                    <div
                                        id="core-email-branding-preview-social"
                                        class="mt-4 hidden flex-wrap items-center justify-center gap-3 text-xs font-semibold"
                                    ></div>

                                    <div
                                        id="core-email-branding-preview-unsubscribe"
                                        class="mx-auto mt-5 max-w-xl border-t border-slate-200 pt-4 text-center"
                                    >
                                        <a
                                            href="#"
                                            onclick="return false;"
                                            class="text-xs font-medium text-slate-500 underline"
                                        >
                                            Unsubscribe from these emails
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

    {{-- ESUBIZ_CORE_MAIL_ZERO_MAILBOX_FOLDERS_V4:
         Level-2 workspace intentionally remains visible with zero mailboxes. --}}
</div>

{{-- ESUBIZ_CORE_EMAIL_BRANDING_TAB_JS_V3 --}}
<script>
document.addEventListener('DOMContentLoaded', () => {
    const brandingTab =
        document.querySelector('[data-email-section="branding"]');

    const brandingPanel =
        document.getElementById('core-email-branding-panel');

    if (!brandingTab || !brandingPanel) {
        return;
    }

    const sectionTabs =
        Array.from(document.querySelectorAll('.core-email-section'));

    const listPanel =
        document.getElementById('core-email-list-panel');

    const composePanel =
        document.getElementById('core-email-compose-panel');

    const settingsPanel =
        document.getElementById('core-email-settings-panel');

    function selectBranding() {
        sectionTabs.forEach(tab => {
            const active = tab === brandingTab;

            tab.classList.toggle('bg-slate-900', active);
            tab.classList.toggle('text-white', active);

            if (!active) {
                tab.classList.remove('bg-slate-900', 'text-white');
                tab.classList.add('text-slate-600');
            }
        });

        listPanel?.classList.add('hidden');
        composePanel?.classList.add('hidden');
        settingsPanel?.classList.add('hidden');
        brandingPanel.classList.remove('hidden');
    }

    brandingTab.addEventListener(
        'click',
        selectBranding
    );

    sectionTabs
        .filter(tab => tab !== brandingTab)
        .forEach(tab => {
            tab.addEventListener('click', () => {
                brandingPanel.classList.add('hidden');
            });
        });

    const modal =
        document.getElementById(
            'core-email-branding-preview-modal'
        );

    const close =
        document.getElementById(
            'core-email-branding-preview-close'
        );

    const header =
        document.getElementById(
            'core-email-branding-header'
        );

    const footer =
        document.getElementById(
            'core-email-branding-footer'
        );

    const signature =
        document.getElementById(
            'core-email-branding-signature'
        );

    const background =
        document.getElementById(
            'core-email-branding-background'
        );

    const accent =
        document.getElementById(
            'core-email-branding-accent'
        );

    const width =
        document.getElementById(
            'core-email-branding-width'
        );

    const font =
        document.getElementById(
            'core-email-branding-font'
        );

    const previewHeader =
        document.getElementById(
            'core-email-branding-preview-header'
        );

    const previewFooter =
        document.getElementById(
            'core-email-branding-preview-footer'
        );

    const previewSignature =
        document.getElementById(
            'core-email-branding-preview-signature'
        );

    const previewStage =
        document.getElementById(
            'core-email-branding-preview-stage'
        );

    const previewCard =
        document.getElementById(
            'core-email-branding-preview-card'
        );

    const previewLogo =
        document.getElementById(
            'core-email-branding-preview-logo'
        );

    const logoInput =
        document.getElementById(
            'core-email-branding-logo'
        );

    let temporaryLogoUrl = null;

    const footerContactEnabled =
        document.getElementById('core-email-footer-contact-enabled');

    const footerSocialEnabled =
        document.getElementById('core-email-footer-social-enabled');

    const footerUnsubscribeEnabled =
        document.getElementById('core-email-footer-unsubscribe-enabled');

    const footerContactFields =
        document.getElementById('core-email-footer-contact-fields');

    const footerSocialFields =
        document.getElementById('core-email-footer-social-fields');

    const footerUnsubscribeFields =
        document.getElementById('core-email-footer-unsubscribe-fields');

    const footerPhone =
        document.getElementById('core-email-footer-phone');

    const footerEmail =
        document.getElementById('core-email-footer-email');

    const footerWebsite =
        document.getElementById('core-email-footer-website');

    const footerAddress =
        document.getElementById('core-email-footer-address');

    const footerUnsubscribeText =
        document.getElementById('core-email-footer-unsubscribe-text');

    const previewContact =
        document.getElementById('core-email-branding-preview-contact');

    const previewSocial =
        document.getElementById('core-email-branding-preview-social');

    const previewUnsubscribe =
        document.getElementById('core-email-branding-preview-unsubscribe');

    const socialNetworks = [
        ['facebook', 'Facebook'],
        ['instagram', 'Instagram'],
        ['linkedin', 'LinkedIn'],
        ['x', 'X'],
        ['youtube', 'YouTube'],
        ['tiktok', 'TikTok'],
    ];

    function setFooterGroupState(enabled, container) {
        if (!container) {
            return;
        }

        container.classList.toggle(
            'opacity-50',
            !enabled
        );

        container
            .querySelectorAll('input, textarea, select')
            .forEach(control => {
                control.disabled = !enabled;
            });
    }

    function updateFooterPreview() {
        const showContact =
            footerContactEnabled?.checked ?? true;

        const showSocial =
            footerSocialEnabled?.checked ?? true;

        const showUnsubscribe =
            footerUnsubscribeEnabled?.checked ?? true;

        setFooterGroupState(
            showContact,
            footerContactFields
        );

        setFooterGroupState(
            showSocial,
            footerSocialFields
        );

        setFooterGroupState(
            showUnsubscribe,
            footerUnsubscribeFields
        );

        if (previewContact) {
            const contactItems = [];

            if (footerPhone?.value.trim()) {
                contactItems.push(
                    footerPhone.value.trim()
                );
            }

            if (footerEmail?.value.trim()) {
                contactItems.push(
                    footerEmail.value.trim()
                );
            }

            if (footerWebsite?.value.trim()) {
                contactItems.push(
                    footerWebsite.value.trim()
                );
            }

            if (footerAddress?.value.trim()) {
                contactItems.push(
                    footerAddress.value.trim()
                );
            }

            previewContact.textContent =
                contactItems.join(' • ');

            previewContact.classList.toggle(
                'hidden',
                !showContact
                    || contactItems.length === 0
            );
        }

        if (previewSocial) {
            previewSocial.replaceChildren();

            let socialCount = 0;

            if (showSocial) {
                socialNetworks.forEach(
                    ([key, label]) => {
                        const input =
                            document.getElementById(
                                `core-email-footer-social-${key}`
                            );

                        const url =
                            input?.value.trim();

                        if (!url) {
                            return;
                        }

                        const link =
                            document.createElement('a');

                        link.href = url;
                        link.target = '_blank';
                        link.rel = 'noopener noreferrer';
                        link.textContent = label;
                        link.className =
                            'text-slate-600 underline';

                        previewSocial.appendChild(
                            link
                        );

                        socialCount++;
                    }
                );
            }

            previewSocial.classList.toggle(
                'hidden',
                !showSocial || socialCount === 0
            );

            previewSocial.classList.toggle(
                'flex',
                showSocial && socialCount > 0
            );
        }

        if (previewUnsubscribe) {
            previewUnsubscribe.classList.toggle(
                'hidden',
                !showUnsubscribe
            );

            const link =
                previewUnsubscribe.querySelector('a');

            if (link) {
                link.textContent =
                    footerUnsubscribeText?.value.trim()
                    || 'Unsubscribe from these emails';
            }
        }
    }

    function updatePreview() {
        updateFooterPreview();

        if (previewHeader) {
            previewHeader.textContent =
                header?.value?.trim()
                || 'Thank you for connecting with us.';
        }

        if (previewFooter) {
            previewFooter.textContent =
                footer?.value?.trim() || '';
        }

        if (previewSignature) {
            previewSignature.textContent =
                signature?.value?.trim() || '';
        }

        if (previewStage && background) {
            previewStage.style.backgroundColor =
                background.value;
        }

        if (previewCard && width) {
            previewCard.style.maxWidth =
                `${parseInt(width.value, 10) || 640}px`;
        }

        if (previewLogo && accent) {
            previewLogo.style.color =
                accent.value;
        }

        if (previewCard && font) {
            previewCard.style.fontFamily =
                font.value === 'serif'
                    ? 'Georgia, Cambria, "Times New Roman", serif'
                    : font.value === 'clean'
                        ? 'Arial, Helvetica, sans-serif'
                        : 'ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif';
        }
    }

    function openPreview() {
        if (!modal) {
            return;
        }

        updatePreview();

        modal.classList.remove('hidden');
        modal.classList.add('flex');

        document.body.classList.add('overflow-hidden');
    }

    function closePreview() {
        if (!modal) {
            return;
        }

        modal.classList.add('hidden');
        modal.classList.remove('flex');

        document.body.classList.remove('overflow-hidden');
    }

    document
        .querySelectorAll(
            '#core-email-branding-preview, [data-core-email-branding-preview]'
        )
        .forEach(button => {
            button.addEventListener(
                'click',
                openPreview
            );
        });

    close?.addEventListener(
        'click',
        closePreview
    );

    modal?.addEventListener(
        'click',
        event => {
            if (event.target === modal) {
                closePreview();
            }
        }
    );

    document.addEventListener(
        'keydown',
        event => {
            if (
                event.key === 'Escape'
                && modal
                && !modal.classList.contains('hidden')
            ) {
                closePreview();
            }
        }
    );

    [
        header,
        footer,
        signature,
        background,
        accent,
        width,
        font,
        footerContactEnabled,
        footerSocialEnabled,
        footerUnsubscribeEnabled,
        footerPhone,
        footerEmail,
        footerWebsite,
        footerAddress,
        footerUnsubscribeText,
        ...socialNetworks.map(
            ([key]) =>
                document.getElementById(
                    `core-email-footer-social-${key}`
                )
        )
    ].forEach(control => {
        control?.addEventListener(
            'input',
            updatePreview
        );

        control?.addEventListener(
            'change',
            updatePreview
        );
    });

    logoInput?.addEventListener(
        'change',
        () => {
            const file =
                logoInput.files?.[0];

            if (!file) {
                return;
            }

            if (temporaryLogoUrl) {
                URL.revokeObjectURL(
                    temporaryLogoUrl
                );
            }

            temporaryLogoUrl =
                URL.createObjectURL(file);

            const html =
                `<img src="${temporaryLogoUrl}" alt="Email logo" class="mx-auto max-h-16 max-w-[220px] object-contain">`;

            const localPreview =
                document.getElementById(
                    'core-email-branding-logo-preview'
                );

            if (localPreview) {
                localPreview.innerHTML = html;
            }

            if (previewLogo) {
                previewLogo.innerHTML = html;
            }
        }
    );

    updatePreview();
});
</script>

{{-- ESUBIZ_CORE_EMAIL_SENDER_SELECTOR_JS_V3 --}}
<script>
document.addEventListener('DOMContentLoaded', () => {
    const root =
        document.getElementById('core-email-workspace');

    const toggle =
        document.getElementById('core-email-sender-toggle');

    if (!root || !toggle) {
        return;
    }

    const csrf =
        document.querySelector('meta[name="csrf-token"]')
            ?.getAttribute('content') || '';

    const knob =
        document.getElementById('core-email-sender-knob');

    const defaultLabel =
        document.getElementById('core-email-default-label');

    const premiumLabel =
        document.getElementById('core-email-premium-label');

    const premiumPanel =
        document.getElementById('core-email-premium-panel');

    const balance =
        document.getElementById('core-email-credit-balance');

    const logBody =
        document.getElementById('core-email-credit-log');

    const logPrevious =
        document.getElementById('core-email-credit-previous');

    const logNext =
        document.getElementById('core-email-credit-next');

    const logPage =
        document.getElementById('core-email-credit-page');

    const modal =
        document.getElementById('core-email-info-modal');

    const modalTitle =
        document.getElementById('core-email-info-title');

    const modalDescription =
        document.getElementById('core-email-info-description');

    const modalContent =
        document.getElementById('core-email-info-content');

    const modalClose =
        document.getElementById('core-email-info-close');

    const services = @json($emailServiceData['services'] ?? []);

    let premium =
        @json($emailSenderTransport === 'premium_smtp');

    let creditPage = 1;

    function paintToggle() {
        toggle.setAttribute(
            'aria-checked',
            premium ? 'true' : 'false'
        );

        toggle.classList.toggle('bg-blue-600', premium);
        toggle.classList.toggle('bg-slate-300', !premium);

        knob.classList.toggle('left-8', premium);
        knob.classList.toggle('left-1', !premium);

        defaultLabel.classList.toggle(
            'text-slate-900',
            !premium
        );

        defaultLabel.classList.toggle(
            'text-slate-400',
            premium
        );

        premiumLabel.classList.toggle(
            'text-blue-600',
            premium
        );

        premiumLabel.classList.toggle(
            'text-slate-400',
            !premium
        );

        premiumPanel.classList.toggle(
            'hidden',
            !premium
        );
    }

    function renderLogs(data) {
        const rows =
            data.premium?.transactions || [];

        balance.textContent =
            Number(
                data.premium?.balance || 0
            ).toLocaleString();

        if (!rows.length) {
            logBody.innerHTML =
                '<tr><td colspan="4" class="px-4 py-8 text-center text-slate-500">No Email Credit activity yet.</td></tr>';
        } else {
            logBody.innerHTML = rows.map(row => `
                <tr>
                    <td class="whitespace-nowrap px-4 py-3 text-slate-600">
                        ${escapeHtml(row.created_at || '')}
                    </td>
                    <td class="px-4 py-3 font-medium text-slate-700">
                        ${escapeHtml(row.direction || '')}
                    </td>
                    <td class="px-4 py-3 text-slate-700">
                        ${Number(row.amount || 0).toLocaleString()}
                    </td>
                    <td class="px-4 py-3 text-slate-700">
                        ${Number(row.balance_after || 0).toLocaleString()}
                    </td>
                </tr>
            `).join('');
        }

        const pagination =
            data.premium?.pagination || {};

        creditPage =
            Number(pagination.page || 1);

        logPage.textContent =
            `Page ${creditPage}`;

        logPrevious.disabled =
            !pagination.has_previous;

        logNext.disabled =
            !pagination.has_next;
    }

    function escapeHtml(value) {
        const div =
            document.createElement('div');

        div.textContent =
            String(value ?? '');

        return div.innerHTML;
    }

    async function loadPremium(page = 1) {
        const url = new URL(
            root.dataset.premiumUrl,
            window.location.origin
        );

        url.searchParams.set('page', page);

        const response = await fetch(url, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            },
            credentials: 'same-origin',
        });

        if (!response.ok) {
            throw new Error(
                'Unable to load Email Credit data.'
            );
        }

        renderLogs(
            await response.json()
        );
    }

    async function saveMode(nextPremium) {
        const response = await fetch(
            root.dataset.senderModeUrl,
            {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrf,
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                },
                credentials: 'same-origin',
                body: JSON.stringify({
                    transport:
                        nextPremium
                            ? 'premium_smtp'
                            : 'php',
                }),
            }
        );

        if (!response.ok) {
            throw new Error(
                'Unable to change email sender.'
            );
        }

        premium = nextPremium;
        paintToggle();

        if (premium) {
            await loadPremium(1);
        }
    }

    toggle.addEventListener('click', async () => {
        toggle.disabled = true;

        try {
            await saveMode(!premium);
        } catch (error) {
            window.alert(error.message);
        } finally {
            toggle.disabled = false;
        }
    });

    document
        .querySelectorAll('[data-email-info]')
        .forEach(button => {
            button.addEventListener('click', () => {
                const service =
                    services[
                        button.dataset.emailInfo
                    ];

                if (!service) {
                    return;
                }

                modalTitle.textContent =
                    service.name || '';

                modalDescription.textContent =
                    service.description || '';

                modalContent.textContent =
                    service.info_content || '';

                modal.classList.remove('hidden');
                modal.classList.add('flex');
            });
        });

    function closeModal() {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    modalClose?.addEventListener(
        'click',
        closeModal
    );

    modal?.addEventListener('click', event => {
        if (event.target === modal) {
            closeModal();
        }
    });

    logPrevious?.addEventListener(
        'click',
        () => {
            if (creditPage > 1) {
                loadPremium(creditPage - 1)
                    .catch(() => {});
            }
        }
    );

    logNext?.addEventListener(
        'click',
        () => {
            loadPremium(creditPage + 1)
                .catch(() => {});
        }
    );

    document
        .querySelectorAll('[data-email-package]')
        .forEach(button => {
            button.addEventListener('click', () => {
                /*
                 * Checkout wiring belongs to the existing Central
                 * credit-package purchase flow. Do not create a
                 * duplicate payment implementation here.
                 */
                window.dispatchEvent(
                    new CustomEvent(
                        'esubiz:purchase-credit-package',
                        {
                            detail: {
                                service: 'email',
                                packageId:
                                    Number(
                                        button.dataset.emailPackage
                                    ),
                            },
                        }
                    )
                );
            });
        });

    paintToggle();

    if (premium) {
        loadPremium(1).catch(() => {});
    }
});
</script>

@if($mailboxes->isNotEmpty())
<script>
document.addEventListener('DOMContentLoaded', () => {
    const root =
        document.getElementById('core-email-workspace');

    if (!root) {
        return;
    }

    let mailboxId = @json($selectedMailboxId);
    let folder = 'inbox';
    let page = 1;
    let search = '';
    let timer = null;

    const mailboxTabs = Array.from(
        root.querySelectorAll('.core-mailbox-tab')
    );

    const sectionTabs = Array.from(
        root.querySelectorAll('.core-email-section')
    );

    const listPanel =
        document.getElementById('core-email-list-panel');

    const composePanel =
        document.getElementById('core-email-compose-panel');

    const settingsPanel =
        document.getElementById('core-email-settings-panel');

    const results =
        document.getElementById('core-email-message-results');

    const previous =
        document.getElementById('core-email-previous');

    const next =
        document.getElementById('core-email-next');

    const pageLabel =
        document.getElementById('core-email-page-label');

    const searchInput =
        document.getElementById('core-email-search');

    const searchDropdown =
        document.getElementById('core-email-search-dropdown');

    const searchResults =
        document.getElementById('core-email-search-results');

    const searchSpinner =
        document.getElementById('core-email-search-spinner');

    let searchRequestController = null;

    const folderTitle =
        document.getElementById('core-email-folder-title');

    const activeAddress =
        document.getElementById('core-email-active-address');

    const composeAddress =
        document.getElementById('core-email-compose-address');

    const settingsAddress =
        document.getElementById('core-email-settings-address');

    const settingsEmail =
        document.getElementById('core-email-settings-email');

    const folderLabels = {
        inbox: 'Inbox',
        spam: 'Spam',
        draft: 'Draft',
        sent: 'Sent',
        trash: 'Trash',
    };

    function currentMailbox() {
        return mailboxTabs.find(
            tab =>
                Number(tab.dataset.mailboxId) ===
                Number(mailboxId)
        );
    }

    function refreshAddress() {
        const tab = currentMailbox();

        const address =
            tab ? tab.dataset.mailboxAddress : '';

        activeAddress.textContent = address;
        composeAddress.textContent = address;
        settingsAddress.textContent = address;

        if (settingsEmail) {
            settingsEmail.value = address;
        }
    }

    async function loadMessages() {
        if (!mailboxId) {
            return;
        }

        results.innerHTML =
            '<div class="py-10 text-center text-sm text-slate-500">Loading emails...</div>';

        const url = new URL(
            root.dataset.messagesUrl,
            window.location.origin
        );

        url.searchParams.set('mailbox', mailboxId);
        url.searchParams.set('folder', folder);
        url.searchParams.set('page', page);

        if (search) {
            url.searchParams.set('search', search);
        }

        try {
            const response = await fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                },
                credentials: 'same-origin',
            });

            if (!response.ok) {
                throw new Error('Email request failed.');
            }

            const data = await response.json();

            results.innerHTML = data.html;

            page = Number(data.page || 1);

            previous.disabled = !data.has_previous;
            next.disabled = !data.has_next;

            pageLabel.textContent = `Page ${page}`;
        } catch (error) {
            results.innerHTML =
                '<div class="rounded-xl border border-red-200 bg-red-50 px-4 py-4 text-sm text-red-700">Unable to load emails.</div>';

            previous.disabled = true;
            next.disabled = true;
        }
    }

    function activateMailbox(tab) {
        mailboxTabs.forEach(item => {
            item.classList.remove(
                'bg-slate-900',
                'text-white'
            );

            item.classList.add(
                'bg-white',
                'text-slate-600'
            );
        });

        tab.classList.remove(
            'bg-white',
            'text-slate-600'
        );

        tab.classList.add(
            'bg-slate-900',
            'text-white'
        );

        mailboxId = Number(tab.dataset.mailboxId);
        page = 1;

        refreshAddress();

        if (!listPanel.classList.contains('hidden')) {
            loadMessages();
        }
    }

    function activateSection(tab) {
        const section = tab.dataset.emailSection;

        sectionTabs.forEach(item => {
            item.classList.remove(
                'bg-slate-900',
                'text-white'
            );

            item.classList.add('text-slate-600');
        });

        tab.classList.remove('text-slate-600');

        tab.classList.add(
            'bg-slate-900',
            'text-white'
        );

        listPanel.classList.add('hidden');
        composePanel.classList.add('hidden');
        settingsPanel.classList.add('hidden');

        if (section === 'compose') {
            composePanel.classList.remove('hidden');
            return;
        }

        if (section === 'settings') {
            settingsPanel.classList.remove('hidden');
            return;
        }

        if (
            section === 'compose'
            && !openingExistingDraft
        ) {
            resetDraftEditing();
        }

        folder = section;
        page = 1;

        folderTitle.textContent =
            folderLabels[folder] || 'Inbox';

        listPanel.classList.remove('hidden');

        loadMessages();
    }

    mailboxTabs.forEach(tab => {
        tab.addEventListener(
            'click',
            () => activateMailbox(tab)
        );
    });

    sectionTabs.forEach(tab => {
        tab.addEventListener(
            'click',
            () => activateSection(tab)
        );
    });

    previous.addEventListener('click', () => {
        if (page <= 1) {
            return;
        }

        page -= 1;
        loadMessages();
    });

    next.addEventListener('click', () => {
        page += 1;
        loadMessages();
    });

    /*
     * ESUBIZ_CORE_EMAIL_DRAFT_EDIT_JS_V2
     */
    let activeDraftId = null;
    let openingExistingDraft = false;


    const existingAttachments =
        document.getElementById(
            'core-email-compose-existing-attachments'
        );

    const removeAttachmentUrlTemplate =
        root.dataset
            .removeDraftAttachmentUrlTemplate
        || '';

    const renderDraftAttachments =
        attachments => {
            if (!existingAttachments) {
                return;
            }

            const items =
                Array.isArray(attachments)
                    ? attachments
                    : [];

            if (!items.length) {
                existingAttachments.innerHTML = '';
                existingAttachments.classList.add(
                    'hidden'
                );
                return;
            }

            existingAttachments.classList.remove(
                'hidden'
            );

            existingAttachments.innerHTML =
                items.map(attachment => {
                    const id =
                        Number(
                            attachment.id
                        ) || 0;

                    const name =
                        escapeSearchHtml(
                            attachment.name
                            || 'Attachment'
                        );

                    return `
                        <div
                            class="flex items-center justify-between gap-3 rounded-xl border border-slate-200 bg-slate-50 px-3 py-2"
                            data-existing-attachment-id="${id}"
                        >
                            <div class="min-w-0 truncate text-sm font-medium text-slate-700">
                                ${name}
                            </div>

                            <button
                                type="button"
                                class="core-email-remove-draft-attachment shrink-0 text-xs font-semibold text-red-600"
                                data-attachment-id="${id}"
                            >
                                Remove
                            </button>
                        </div>
                    `;
                }).join('');
        };


    const resetDraftEditing = () => {
        activeDraftId = null;
        renderDraftAttachments([]);
    };

    const openDraftInCompose = message => {
        openingExistingDraft = true;
        activeDraftId =
            Number(message.id) || null;

        if (composeTo) {
            composeTo.value =
                message.to || '';
        }

        if (composeCc) {
            composeCc.value =
                message.cc || '';
        }

        if (composeBcc) {
            composeBcc.value =
                message.bcc || '';
        }

        if (composeSubject) {
            composeSubject.value =
                message.subject === '(No subject)'
                    ? ''
                    : (
                        message.subject
                        || ''
                    );
        }

        if (composeBody) {
            if (message.body_html) {
                composeBody.innerHTML =
                    message.body_html;
            } else {
                composeBody.textContent =
                    message.body || '';
            }
        }

        renderDraftAttachments(
            message.attachments || []
        );

        composeStatus?.classList.add(
            'hidden'
        );

        /*
         * Reuse the existing Level-2 Compose tab behavior
         * instead of creating a second editor.
         */
        const composeTab =
            document.querySelector(
                '[data-email-section="compose"]'
            );

        composeTab?.click();

        openingExistingDraft = false;
    };

    /*
     * ESUBIZ_CORE_EMAIL_SAVE_DRAFT_JS_V1
     */
    const composeTo =
        document.getElementById(
            'core-email-compose-to'
        );

    const composeCc =
        document.getElementById(
            'core-email-compose-cc'
        );

    const composeBcc =
        document.getElementById(
            'core-email-compose-bcc'
        );

    const composeSubject =
        document.getElementById(
            'core-email-compose-subject'
        );

    const composeBody =
        document.getElementById(
            'core-email-compose-body'
        );

    const composeAttachments =
        document.getElementById(
            'core-email-compose-attachments'
        );

    const saveDraftButton =
        document.getElementById(
            'core-email-save-draft'
        );

    const composeStatus =
        document.getElementById(
            'core-email-compose-status'
        );

    const showComposeStatus = (
        message,
        success = false
    ) => {
        if (!composeStatus) {
            return;
        }

        composeStatus.textContent = message;

        composeStatus.className =
            success
                ? 'rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700'
                : 'rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700';
    };

    const csrfToken =
        document.querySelector(
            'meta[name="csrf-token"]'
        )?.getAttribute('content') || '';

    saveDraftButton?.addEventListener(
        'click',
        async () => {
            if (!mailboxId) {
                showComposeStatus(
                    'Select an email address first.'
                );
                return;
            }

            saveDraftButton.disabled = true;

            const originalLabel =
                saveDraftButton.textContent;

            saveDraftButton.textContent =
                'Saving...';

            try {
                const formData =
                    new FormData();

                formData.append(
                    'mailbox',
                    String(
                        Number(mailboxId)
                    )
                );

                formData.append(
                    'to',
                    composeTo?.value || ''
                );

                formData.append(
                    'cc',
                    composeCc?.value || ''
                );

                formData.append(
                    'bcc',
                    composeBcc?.value || ''
                );

                formData.append(
                    'subject',
                    composeSubject?.value || ''
                );

                formData.append(
                    'body',
                    composeBody?.innerText || ''
                );

                formData.append(
                    'body_html',
                    composeBody?.innerHTML || ''
                );

                if (activeDraftId) {
                    formData.append(
                        'draft_id',
                        String(activeDraftId)
                    );
                }

                Array.from(
                    composeAttachments?.files || []
                ).forEach(file => {
                    formData.append(
                        'attachments[]',
                        file
                    );
                });

                const response = await fetch(
                    root.dataset.saveDraftUrl,
                    {
                        method: 'POST',
                        headers: {
                            'Accept':
                                'application/json',
                            'X-Requested-With':
                                'XMLHttpRequest',
                            'X-CSRF-TOKEN':
                                csrfToken,
                        },
                        body: formData,
                    }
                );

                const payload =
                    await response.json()
                        .catch(() => ({}));

                if (!response.ok) {
                    throw new Error(
                        payload.message
                        || 'Draft could not be saved.'
                    );
                }

                activeDraftId =
                    Number(
                        payload.draft_id
                    ) || activeDraftId;

                if (composeAttachments) {
                    composeAttachments.value = '';
                }

                showComposeStatus(
                    payload.message
                    || 'Draft saved.',
                    true
                );

                /*
                 * Draft folder refreshes through the same
                 * canonical 10/page AJAX folder loader when
                 * the administrator opens Draft.
                 */
            } catch (error) {
                showComposeStatus(
                    error.message
                    || 'Draft could not be saved.'
                );
            } finally {
                saveDraftButton.disabled = false;
                saveDraftButton.textContent =
                    originalLabel;
            }
        }
    );

    /*
     * ESUBIZ_CORE_EMAIL_DRAFT_ATTACHMENT_REMOVE_JS_V1
     */
    existingAttachments?.addEventListener(
        'click',
        async event => {
            const button =
                event.target.closest(
                    '.core-email-remove-draft-attachment'
                );

            if (
                !button
                || !activeDraftId
                || !removeAttachmentUrlTemplate
            ) {
                return;
            }

            const attachmentId =
                Number(
                    button.dataset.attachmentId
                );

            if (!attachmentId) {
                return;
            }

            button.disabled = true;

            try {
                const url =
                    new URL(
                        removeAttachmentUrlTemplate,
                        window.location.origin
                    );

                url.pathname =
                    url.pathname.replace(
                        /\/0\/attachments\/0$/,
                        `/${activeDraftId}/attachments/${attachmentId}`
                    );

                const response =
                    await fetch(
                        url.toString(),
                        {
                            method: 'DELETE',
                            headers: {
                                'Accept':
                                    'application/json',
                                'Content-Type':
                                    'application/json',
                                'X-Requested-With':
                                    'XMLHttpRequest',
                                'X-CSRF-TOKEN':
                                    csrfToken,
                            },
                            body: JSON.stringify({
                                mailbox:
                                    Number(mailboxId),
                            }),
                        }
                    );

                const payload =
                    await response.json()
                        .catch(() => ({}));

                if (!response.ok) {
                    throw new Error(
                        payload.message
                        || 'Attachment could not be removed.'
                    );
                }

                button.closest(
                    '[data-existing-attachment-id]'
                )?.remove();

                if (
                    existingAttachments
                    && !existingAttachments.querySelector(
                        '[data-existing-attachment-id]'
                    )
                ) {
                    existingAttachments.classList.add(
                        'hidden'
                    );
                }

                showComposeStatus(
                    payload.message
                    || 'Attachment removed.',
                    true
                );
            } catch (error) {
                button.disabled = false;

                showComposeStatus(
                    error.message
                    || 'Attachment could not be removed.'
                );
            }
        }
    );

    /*
     * ESUBIZ_CORE_EMAIL_MESSAGE_VIEW_JS_V1
     *
     * Folder rows and AJAX search results share this viewer.
     */
    const messageViewPanel =
        document.getElementById(
            'core-email-message-view-panel'
        );

    const messageListPanel =
        document.getElementById(
            'core-email-list-panel'
        );

    const messageBack =
        document.getElementById(
            'core-email-message-back'
        );

    const messageViewLoading =
        document.getElementById(
            'core-email-view-loading'
        );

    const messageViewContent =
        document.getElementById(
            'core-email-view-content'
        );

    const formatMessageDate = value => {
        if (!value) {
            return '';
        }

        const parsed = new Date(value);

        if (Number.isNaN(parsed.getTime())) {
            return String(value);
        }

        return parsed.toLocaleString();
    };

    const openMessage = async messageId => {
        messageId = Number(messageId);

        if (!messageId) {
            return;
        }

        closeSearchDropdown();

        messageListPanel?.classList.add('hidden');
        messageViewPanel?.classList.remove('hidden');

        messageViewLoading?.classList.remove('hidden');
        messageViewContent?.classList.add('hidden');

        const template =
            root.dataset.messageUrlTemplate;

        if (!template) {
            return;
        }

        const url = new URL(
            template.replace(
                /\/0(?:\?|$)/,
                `/${messageId}$1`
            ),
            window.location.origin
        );

        url.searchParams.set(
            'mailbox',
            String(mailboxId)
        );

        try {
            const response = await fetch(
                url.toString(),
                {
                    headers: {
                        'Accept':
                            'application/json',
                        'X-Requested-With':
                            'XMLHttpRequest',
                    },
                }
            );

            if (!response.ok) {
                throw new Error(
                    'Unable to open email.'
                );
            }

            const payload =
                await response.json();

            const message =
                payload.message || {};

            /*
             * Draft rows use the same secure detail endpoint,
             * then reopen inside the canonical Compose editor.
             */
            if (
                message.folder === 'draft'
            ) {
                messageViewPanel
                    ?.classList.add('hidden');

                messageListPanel
                    ?.classList.remove('hidden');

                message.attachments =
                    Array.isArray(
                        payload.attachments
                    )
                        ? payload.attachments
                        : [];

                openDraftInCompose(message);
                return;
            }

            document.getElementById(
                'core-email-view-subject'
            ).textContent =
                message.subject || '(No subject)';

            document.getElementById(
                'core-email-view-from'
            ).textContent =
                message.from || '';

            document.getElementById(
                'core-email-view-to'
            ).textContent =
                message.to || '';

            const ccRow =
                document.getElementById(
                    'core-email-view-cc-row'
                );

            const cc =
                document.getElementById(
                    'core-email-view-cc'
                );

            if (message.cc) {
                cc.textContent = message.cc;
                ccRow.classList.remove('hidden');
            } else {
                cc.textContent = '';
                ccRow.classList.add('hidden');
            }

            document.getElementById(
                'core-email-view-source'
            ).textContent =
                message.source_label
                    || 'Direct Email';

            document.getElementById(
                'core-email-view-date'
            ).textContent =
                formatMessageDate(
                    message.received_at
                    || message.sent_at
                    || message.created_at
                );

            const body =
                document.getElementById(
                    'core-email-view-body'
                );

            /*
             * body_html is canonical mail content when
             * available. It originates from the mailbox
             * ledger, not from search-result HTML.
             */
            if (message.body_html) {
                body.innerHTML =
                    message.body_html;
            } else {
                body.textContent =
                    message.body || '';
            }

            const attachments =
                Array.isArray(
                    payload.attachments
                )
                    ? payload.attachments
                    : [];

            const attachmentSection =
                document.getElementById(
                    'core-email-view-attachments-section'
                );

            const attachmentList =
                document.getElementById(
                    'core-email-view-attachments'
                );

            if (attachments.length) {
                attachmentList.innerHTML =
                    attachments.map(item => `
                        <div class="rounded-xl border border-slate-200 p-3">
                            <div class="truncate text-sm font-semibold text-slate-800">
                                ${escapeSearchHtml(item.name)}
                            </div>

                            <div class="mt-1 text-xs text-slate-500">
                                ${item.size
                                    ? `${Math.ceil(item.size / 1024)} KB`
                                    : 'Attachment'}
                            </div>
                        </div>
                    `).join('');

                attachmentSection
                    .classList.remove('hidden');
            } else {
                attachmentList.innerHTML = '';
                attachmentSection
                    .classList.add('hidden');
            }

            messageViewLoading
                ?.classList.add('hidden');

            messageViewContent
                ?.classList.remove('hidden');
        } catch (error) {
            if (messageViewLoading) {
                messageViewLoading.textContent =
                    'This email could not be opened.';
            }
        }
    };

    document.addEventListener(
        'click',
        event => {
            const target = event.target.closest(
                '.core-email-message-row, .core-email-search-result'
            );

            if (!target) {
                return;
            }

            openMessage(
                target.dataset.messageId
            );
        }
    );

    messageBack?.addEventListener(
        'click',
        () => {
            messageViewPanel
                ?.classList.add('hidden');

            messageListPanel
                ?.classList.remove('hidden');

            loadMessages();
        }
    );

    /*
     * ESUBIZ_CORE_MAIL_FOLDER_SEARCH_JS_V3
     * ESUBIZ_CORE_MAIL_FOLDER_SEARCH_PARAM_V4
     *
     * Search is scoped to the currently selected mailbox and
     * currently viewed message folder.
     *
     * Results appear in a dropdown and do not replace the
     * normal folder list.
     */
    const escapeSearchHtml = value => {
        const node = document.createElement('div');
        node.textContent = value ?? '';
        return node.innerHTML;
    };

    const closeSearchDropdown = () => {
        searchDropdown?.classList.add('hidden');
    };

    const renderSearchResults = payload => {
        if (!searchResults || !searchDropdown) {
            return;
        }

        const rows = Array.isArray(payload.messages)
            ? payload.messages
            : [];

        if (!rows.length) {
            searchResults.innerHTML = `
                <div class="px-4 py-5 text-center text-sm text-slate-500">
                    No matching emails found in this folder.
                </div>
            `;

            searchDropdown.classList.remove('hidden');
            return;
        }

        searchResults.innerHTML = rows.map(message => {
            const sender =
                message.counterparty
                || message.from
                || message.from_email
                || message.sender
                || '';

            const subject =
                message.subject
                || '(No subject)';

            const preview =
                message.preview
                || message.snippet
                || message.body_preview
                || '';

            const date =
                message.received_at
                || message.sent_at
                || message.created_at
                || '';

            return `
                <button
                    type="button"
                    class="core-email-search-result block w-full px-4 py-3 text-left transition hover:bg-slate-50"
                    data-message-id="${Number(message.id) || 0}"
                >
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0 flex-1">
                            <div class="truncate text-sm font-semibold text-slate-900">
                                ${escapeSearchHtml(sender)}
                            </div>

                            <div class="mt-0.5 truncate text-sm text-slate-700">
                                ${escapeSearchHtml(subject)}
                            </div>

                            ${
                                preview
                                    ? `<div class="mt-1 truncate text-xs text-slate-500">${escapeSearchHtml(preview)}</div>`
                                    : ''
                            }
                        </div>

                        ${
                            date
                                ? `<div class="shrink-0 text-[11px] text-slate-400">${escapeSearchHtml(date)}</div>`
                                : ''
                        }
                    </div>
                </button>
            `;
        }).join('');

        searchDropdown.classList.remove('hidden');
    };

    const searchCurrentFolder = async value => {
        const term = value.trim();

        if (!term) {
            closeSearchDropdown();
            return;
        }

        if (searchRequestController) {
            searchRequestController.abort();
        }

        searchRequestController =
            new AbortController();

        searchSpinner?.classList.remove('hidden');
        searchSpinner?.classList.add('flex');

        try {
            const url = new URL(
                root.dataset.messagesUrl,
                window.location.origin
            );

            url.searchParams.set(
                'mailbox',
                String(mailboxId)
            );

            url.searchParams.set(
                'folder',
                folder
            );

            url.searchParams.set(
                'search',
                term
            );

            /*
             * Dropdown results use the same canonical
             * folder-scoped backend search endpoint.
             * The endpoint itself remains fixed at 10/page.
             */
            url.searchParams.set(
                'page',
                '1'
            );

            const response = await fetch(
                url.toString(),
                {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With':
                            'XMLHttpRequest',
                    },
                    signal:
                        searchRequestController.signal,
                }
            );

            if (!response.ok) {
                throw new Error(
                    'Folder search failed.'
                );
            }

            const payload =
                await response.json();

            /*
             * Ignore a late response if the user changed
             * the search text while the request was running.
             */
            if (
                searchInput.value.trim()
                !== term
            ) {
                return;
            }

            renderSearchResults(payload);
        } catch (error) {
            if (error.name === 'AbortError') {
                return;
            }

            if (searchResults && searchDropdown) {
                searchResults.innerHTML = `
                    <div class="px-4 py-5 text-center text-sm text-red-600">
                        Search could not be completed.
                    </div>
                `;

                searchDropdown.classList.remove(
                    'hidden'
                );
            }
        } finally {
            searchSpinner?.classList.add('hidden');
            searchSpinner?.classList.remove('flex');
        }
    };

    searchInput.addEventListener(
        'input',
        () => {
            clearTimeout(timer);

            const value =
                searchInput.value;

            if (!value.trim()) {
                closeSearchDropdown();
                return;
            }

            timer = setTimeout(
                () => {
                    searchCurrentFolder(value);
                },
                300
            );
        }
    );

    searchInput.addEventListener(
        'focus',
        () => {
            if (searchInput.value.trim()) {
                searchCurrentFolder(
                    searchInput.value
                );
            }
        }
    );

    document.addEventListener(
        'click',
        event => {
            if (
                !event.target.closest(
                    '#core-email-search'
                )
                && !event.target.closest(
                    '#core-email-search-dropdown'
                )
            ) {
                closeSearchDropdown();
            }
        }
    );

    refreshAddress();
    loadMessages();
});
</script>
@endif

@endsection
