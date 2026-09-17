@extends('tenant.admin.layouts.app')

@section('title', 'Email')

@section('content')

{{-- ESUBIZ_CORE_GENERAL_EMAIL_PAGE_V1 --}}

<div
    id="core-email-workspace"
    class="mx-auto max-w-7xl"
    data-messages-url="{{ route('tenant.cms.email.messages', ['subdomain' => request()->route('subdomain')]) }}"
    data-refresh-url="{{ route('tenant.cms.email.refresh', ['subdomain' => request()->route('subdomain')]) }}"
    data-save-draft-url="{{ route('tenant.cms.email.drafts.store', ['subdomain' => request()->route('subdomain')]) }}"
    data-send-url="{{ route('tenant.cms.email.send', ['subdomain' => request()->route('subdomain')]) }}"
    data-remove-draft-attachment-url-template="{{ route('tenant.cms.email.drafts.attachments.destroy', ['subdomain' => request()->route('subdomain'), 'message' => 0, 'attachment' => 0]) }}"
    data-message-url-template="{{ route('tenant.cms.email.message', ['subdomain' => request()->route('subdomain'), 'message' => 0]) }}"
    data-message-action-url-template="{{ route('core.email.messages.action', ['subdomain' => request()->route('subdomain'), 'message' => 0]) }}"
    data-settings-show-url="{{ route('core.email.settings.show', ['subdomain' => request()->route('subdomain')]) }}"
    data-settings-save-url="{{ route('core.email.settings.save', ['subdomain' => request()->route('subdomain')]) }}"
    data-attachment-url-template="{{ route('core.email.attachments.download', ['subdomain' => request()->route('subdomain'), 'attachment' => 0]) }}"
    data-attachment-preview-url-template="{{ route('core.email.attachments.preview', ['subdomain' => request()->route('subdomain'), 'attachment' => 0]) }}"
    data-premium-url="{{ route('tenant.cms.email.premium-data', ['subdomain' => request()->route('subdomain')]) }}"
    data-sender-mode-url="{{ route('tenant.cms.email.sender-mode', ['subdomain' => request()->route('subdomain')]) }}"
>
    {{-- ESUBIZ_CORE_EMAIL_ATTACHMENT_PREVIEW_MODAL_V1 --}}
    <div
        id="core-email-attachment-preview-modal"
        class="fixed inset-0 z-[70] hidden items-center justify-center bg-slate-950/60 p-2 sm:p-4"
        role="dialog"
        aria-modal="true"
        aria-labelledby="core-email-attachment-preview-title"
    >
        <div
            class="flex max-h-[96vh] w-full max-w-5xl flex-col overflow-hidden rounded-xl bg-white shadow-2xl sm:max-h-[92vh] sm:rounded-2xl"
        >
            <div
                class="flex shrink-0 items-center justify-between gap-3 border-b border-slate-200 px-4 py-3 sm:px-5 sm:py-4"
            >
                <div class="min-w-0">
                    <h2
                        id="core-email-attachment-preview-title"
                        class="truncate text-sm font-bold text-slate-900 sm:text-base"
                    >
                        Attachment Preview
                    </h2>

                    <div
                        id="core-email-attachment-preview-meta"
                        class="mt-0.5 truncate text-xs text-slate-500"
                    ></div>
                </div>

                <button
                    id="core-email-attachment-preview-close"
                    type="button"
                    class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg text-2xl leading-none text-slate-400 transition hover:bg-slate-100 hover:text-slate-700"
                    aria-label="Close attachment preview"
                >
                    &times;
                </button>
            </div>

            <div
                id="core-email-attachment-preview-stage"
                class="min-h-0 flex-1 overflow-auto bg-slate-100 p-2 sm:p-4"
            >
                <iframe
                    id="core-email-attachment-preview-frame"
                    title="Attachment Preview"
                    class="min-h-[65vh] w-full rounded-lg border-0 bg-white sm:min-h-[72vh]"
                ></iframe>
            </div>
        </div>
    </div>

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

        {{-- ESUBIZ_CORE_EMAIL_LEVEL1_MAILBOX_NAV_V1 --}}
    {{--
        LEVEL 1

        Every real mailbox is its own primary tab.

        SaaS:
        + Add Email Address opens Esubiz-managed mailbox
        provisioning/purchase options.

        Off-server:
        + Add Email Address opens external mailbox connection
        setup. It must never invoke Esubiz DirectAdmin.
    --}}
    <div class="mb-4 rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto px-4">
            <div class="flex min-w-max items-end gap-2 pt-4">

                {{-- ESUBIZ_CORE_EMAIL_SENDER_LEVEL1_TAB_V7 --}}
                <button
                    type="button"
                    id="core-email-sender-level1"
                    data-email-level1="sender"
                    class="rounded-t-xl bg-blue-600 px-4 py-3 text-sm font-semibold text-white transition hover:bg-blue-700"
                >
                    Email Sender
                </button>

                @foreach($mailboxes as $mailbox)
                    <button
                        type="button"
                        data-mailbox-id="{{ $mailbox->id }}"
                        data-mailbox-address="{{ $mailbox->email }}"
                        data-email-level1="mailbox"
                        class="core-mailbox-tab rounded-t-xl px-4 py-3 text-sm font-semibold transition
                            {{ (int) $selectedMailboxId === (int) $mailbox->id
                                ? 'bg-blue-800 text-white'
                                : 'bg-blue-600 text-white hover:bg-blue-700' }}"
                    >
                        {{ $mailbox->email }}
                    </button>
                @endforeach

                <button
                    type="button"
                    id="core-email-add-address"
                    data-email-level1="add-address"
                    class="rounded-t-xl bg-blue-600 px-4 py-3 text-sm font-semibold text-white transition hover:bg-blue-700"
                >
                    <span class="mr-1 text-base leading-none">+</span>
                    Add Email Address
                </button>

            </div>
        </div>
    </div>

    {{-- ESUBIZ_CORE_EMAIL_SENDER_LEVEL1_WORKSPACE_V8B --}}
    <div
        id="core-email-sender-workspace"
        class="hidden"
    >
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

        </div>

{{-- ESUBIZ_CORE_EMAIL_ADD_ADDRESS_WORKSPACE_V1 --}}
    <div
        id="core-email-add-address-workspace"
        class="mb-4 hidden rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"
    >
        <div class="mb-6">
            <h2 class="text-lg font-bold text-slate-900">
                Add Email Address
            </h2>

            @if($website->isSaas())
                <p class="mt-1 text-sm text-slate-500">
                    Create an additional email address for this website.
                </p>
            @else
                <p class="mt-1 text-sm text-slate-500">
                    Connect an email address from your own mail server.
                </p>
            @endif
        </div>

        @if($website->isSaas())

            {{--
                ESUBIZ_CORE_SAAS_ADD_MAILBOX_COMMERCE_PLACEHOLDER_V1

                Central Admin will own:
                - included mailbox allowance;
                - additional mailbox price;
                - purchasable quantity/allowance;
                - provisioning availability;
                - related commercial controls.

                Do not hard-code price here.
            --}}
            <div class="rounded-xl border border-blue-200 bg-blue-50 p-5">
                <div class="text-sm font-bold text-slate-900">
                    Additional Email Address
                </div>

                <p class="mt-2 text-sm leading-6 text-slate-600">
                    Additional mailbox pricing and allowance will be supplied by Esubiz Central.
                </p>

                <div
                    id="core-email-add-address-central-options"
                    class="mt-4"
                ></div>
            </div>

        @else

            {{-- ESUBIZ_CORE_OFFSERVER_REAL_MAILBOX_FORM_V2 --}}
            <form
                id="core-email-external-mailbox-form"
                class="max-w-2xl space-y-4"
            >
                @csrf

                <div
                    id="core-email-external-status"
                    class="hidden rounded-xl border px-4 py-3 text-sm"
                ></div>

                <div>
                    <label
                        for="core-email-external-address"
                        class="mb-1.5 block text-sm font-semibold text-slate-700"
                    >
                        Email Address
                    </label>

                    <input
                        id="core-email-external-address"
                        name="email"
                        type="email"
                        autocomplete="username"
                        required
                        placeholder="mail@example.com"
                        class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm outline-none focus:border-blue-500"
                    >
                </div>

                <div>
                    <label
                        for="core-email-external-password"
                        class="mb-1.5 block text-sm font-semibold text-slate-700"
                    >
                        Password
                    </label>

                    <input
                        id="core-email-external-password"
                        name="password"
                        type="password"
                        autocomplete="current-password"
                        required
                        placeholder="Mailbox password"
                        class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm outline-none focus:border-blue-500"
                    >
                </div>

                <input
                    id="core-email-external-manual"
                    name="manual"
                    type="hidden"
                    value="0"
                >

                <div
                    id="core-email-external-manual-fields"
                    class="hidden space-y-5 rounded-xl border border-amber-200 bg-amber-50 p-5"
                >
                    <div>
                        <h3 class="text-sm font-bold text-slate-900">
                            Manual Mail Server Settings
                        </h3>

                        <p class="mt-1 text-sm leading-6 text-slate-600">
                            Automatic setup could not verify this mailbox. Enter the real SMTP and IMAP settings supplied by your email provider.
                        </p>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-3">
                        <div class="sm:col-span-2">
                            <label
                                for="core-email-external-smtp-host"
                                class="mb-1.5 block text-sm font-semibold text-slate-700"
                            >
                                SMTP Server
                            </label>

                            <input
                                id="core-email-external-smtp-host"
                                name="smtp_host"
                                type="text"
                                placeholder="smtp.example.com"
                                class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm outline-none focus:border-blue-500"
                            >
                        </div>

                        <div>
                            <label
                                for="core-email-external-smtp-port"
                                class="mb-1.5 block text-sm font-semibold text-slate-700"
                            >
                                SMTP Port
                            </label>

                            <input
                                id="core-email-external-smtp-port"
                                name="smtp_port"
                                type="number"
                                min="1"
                                max="65535"
                                value="587"
                                class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm outline-none focus:border-blue-500"
                            >
                        </div>
                    </div>

                    <div>
                        <label
                            for="core-email-external-smtp-encryption"
                            class="mb-1.5 block text-sm font-semibold text-slate-700"
                        >
                            SMTP Security
                        </label>

                        <select
                            id="core-email-external-smtp-encryption"
                            name="smtp_encryption"
                            class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm"
                        >
                            <option value="tls">TLS / STARTTLS</option>
                            <option value="ssl">SSL / Implicit TLS</option>
                            <option value="none">None</option>
                        </select>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-3">
                        <div class="sm:col-span-2">
                            <label
                                for="core-email-external-imap-host"
                                class="mb-1.5 block text-sm font-semibold text-slate-700"
                            >
                                IMAP Server
                            </label>

                            <input
                                id="core-email-external-imap-host"
                                name="imap_host"
                                type="text"
                                placeholder="imap.example.com"
                                class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm outline-none focus:border-blue-500"
                            >
                        </div>

                        <div>
                            <label
                                for="core-email-external-imap-port"
                                class="mb-1.5 block text-sm font-semibold text-slate-700"
                            >
                                IMAP Port
                            </label>

                            <input
                                id="core-email-external-imap-port"
                                name="imap_port"
                                type="number"
                                min="1"
                                max="65535"
                                value="993"
                                class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm outline-none focus:border-blue-500"
                            >
                        </div>
                    </div>

                    <div>
                        <label
                            for="core-email-external-imap-encryption"
                            class="mb-1.5 block text-sm font-semibold text-slate-700"
                        >
                            IMAP Security
                        </label>

                        <select
                            id="core-email-external-imap-encryption"
                            name="imap_encryption"
                            class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm"
                        >
                            <option value="ssl">SSL / Implicit TLS</option>
                            <option value="tls">TLS / STARTTLS</option>
                            <option value="none">None</option>
                        </select>
                    </div>
                </div>

                <button
                    type="submit"
                    id="core-email-connect-external-address"
                    class="rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-60"
                >
                    Add Email Address
                </button>
            </form>

            <script>
                /*
                 * ESUBIZ_CORE_OFFSERVER_REAL_MAILBOX_JS_V1
                 *
                 * Password exists only in the browser request and is
                 * encrypted server-side before tenant persistence.
                 */
                document.addEventListener('DOMContentLoaded', function () {
                    const form =
                        document.getElementById(
                            'core-email-external-mailbox-form'
                        );

                    if (!form) {
                        return;
                    }

                    const button =
                        document.getElementById(
                            'core-email-connect-external-address'
                        );

                    const status =
                        document.getElementById(
                            'core-email-external-status'
                        );

                    const manual =
                        document.getElementById(
                            'core-email-external-manual'
                        );

                    const manualFields =
                        document.getElementById(
                            'core-email-external-manual-fields'
                        );

                    const smtpHost =
                        document.getElementById(
                            'core-email-external-smtp-host'
                        );

                    const smtpPort =
                        document.getElementById(
                            'core-email-external-smtp-port'
                        );

                    const smtpEncryption =
                        document.getElementById(
                            'core-email-external-smtp-encryption'
                        );

                    const imapHost =
                        document.getElementById(
                            'core-email-external-imap-host'
                        );

                    const imapPort =
                        document.getElementById(
                            'core-email-external-imap-port'
                        );

                    const imapEncryption =
                        document.getElementById(
                            'core-email-external-imap-encryption'
                        );

                    const setStatus = function (
                        message,
                        success
                    ) {
                        status.textContent = message || '';
                        status.classList.remove(
                            'hidden',
                            'border-green-200',
                            'bg-green-50',
                            'text-green-700',
                            'border-red-200',
                            'bg-red-50',
                            'text-red-700'
                        );

                        status.classList.add(
                            success
                                ? 'border-green-200'
                                : 'border-red-200',

                            success
                                ? 'bg-green-50'
                                : 'bg-red-50',

                            success
                                ? 'text-green-700'
                                : 'text-red-700'
                        );
                    };

                    const applySuggestion = function (
                        suggested
                    ) {
                        if (!suggested) {
                            return;
                        }

                        if (suggested.smtp) {
                            smtpHost.value =
                                suggested.smtp.host || '';

                            smtpPort.value =
                                suggested.smtp.port || 587;

                            smtpEncryption.value =
                                suggested.smtp.encryption
                                    || 'tls';
                        }

                        if (suggested.imap) {
                            imapHost.value =
                                suggested.imap.host || '';

                            imapPort.value =
                                suggested.imap.port || 993;

                            imapEncryption.value =
                                suggested.imap.encryption
                                    || 'ssl';
                        }
                    };

                    form.addEventListener(
                        'submit',
                        async function (event) {
                            event.preventDefault();

                            if (button.disabled) {
                                return;
                            }

                            const originalText =
                                button.textContent;

                            button.disabled = true;
                            button.textContent =
                                manual.value === '1'
                                    ? 'Testing Mail Server...'
                                    : 'Detecting Mail Server...';

                            status.classList.add('hidden');

                            try {
                                const response = await fetch(
                                    @json(route(
                                        'tenant.cms.email.mailboxes.external.store'
                                    )),
                                    {
                                        method: 'POST',

                                        headers: {
                                            'Accept':
                                                'application/json',

                                            'X-Requested-With':
                                                'XMLHttpRequest',
                                        },

                                        body:
                                            new FormData(form),
                                    }
                                );

                                let data = {};

                                try {
                                    data =
                                        await response.json();
                                } catch (error) {
                                    data = {};
                                }

                                if (
                                    response.ok
                                    && data.ok
                                ) {
                                    setStatus(
                                        data.message
                                            || 'Email address connected successfully.',
                                        true
                                    );

                                    button.textContent =
                                        'Connected';

                                    window.setTimeout(
                                        function () {
                                            window.location.reload();
                                        },
                                        650
                                    );

                                    return;
                                }

                                if (
                                    data.manual_required
                                ) {
                                    manual.value = '1';

                                    manualFields.classList.remove(
                                        'hidden'
                                    );

                                    applySuggestion(
                                        data.suggested
                                    );
                                }

                                setStatus(
                                    data.message
                                        || 'The mailbox could not be connected.',
                                    false
                                );

                            } catch (error) {
                                setStatus(
                                    'Core could not reach the mailbox connection service. Please try again.',
                                    false
                                );
                            } finally {
                                if (
                                    button.textContent
                                    !== 'Connected'
                                ) {
                                    button.disabled = false;

                                    button.textContent =
                                        manual.value === '1'
                                            ? 'Test & Add Email Address'
                                            : originalText;
                                }
                            }
                        }
                    );
                });
            </script>
        @endif
    </div>

        {{-- LEVEL 2: SELECTED MAILBOX --}}
        {{-- ESUBIZ_CORE_EMAIL_MAILBOX_WORKSPACE_V1 --}}
        <div
            id="core-email-mailbox-workspace"
            class="rounded-2xl border border-slate-200 bg-white shadow-sm"
        >

            <div class="overflow-x-auto border-b border-slate-200 px-4">
                <div class="flex min-w-max gap-1 py-3">

                    {{-- ESUBIZ_CORE_EMAIL_BLUE_LEVEL2_TABS_V1 --}}
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
                            class="core-email-section rounded-lg px-4 py-2 text-sm font-semibold text-white transition
                                {{ $key === 'inbox'
                                    ? 'bg-blue-800'
                                    : 'bg-blue-600 hover:bg-blue-700' }}"
                        >
                            {{ $label }}
                        </button>

                    @endforeach

                </div>
            </div>

            <div class="p-5">

                {{-- ESUBIZ_CORE_EMAIL_CLEAN_FOLDER_UI_V18 --}}
                <div id="core-email-list-panel">

                    <div class="mb-4 flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">

                        <div class="flex min-w-0 items-center gap-3">
                            <div>
                                <div class="flex items-center gap-2">
                                    <h2
                                        id="core-email-folder-title"
                                        class="text-lg font-bold text-slate-900"
                                    >
                                        Inbox
                                    </h2>

                                    <button
                                        type="button"
                                        id="core-email-folder-refresh"
                                        title="Refresh"
                                        aria-label="Refresh current folder"
                                        class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 bg-white text-base transition hover:bg-slate-50"
                                    >
                                        🔄
                                    </button>
                                </div>

                                <p
                                    id="core-email-active-address"
                                    class="mt-1 text-sm text-slate-500"
                                ></p>
                            </div>
                        </div>

                        {{-- ESUBIZ_CORE_MAIL_FOLDER_AJAX_SEARCH_V3 --}}
                        <div class="relative w-full lg:max-w-sm">
                            <div class="relative">
                                <input
                                    id="core-email-search"
                                    type="search"
                                    autocomplete="off"
                                    placeholder="Search this folder..."
                                    class="w-full rounded-xl border border-slate-300 px-4 py-2.5 pr-10 text-sm outline-none focus:border-blue-500"
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

                    <div
                        id="core-email-message-results"
                        class="overflow-hidden rounded-xl border border-slate-200 bg-white"
                    >
                        <div class="py-12 text-center text-sm text-slate-500">
                            Loading emails...
                        </div>
                    </div>

                    <div class="mt-4 flex items-center justify-between">
                        <button
                            id="core-email-previous"
                            type="button"
                            disabled
                            class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-40"
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
                            class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-40"
                        >
                            Next
                        </button>
                    </div>

                </div>

                {{-- ESUBIZ_CORE_EMAIL_CLEAN_MESSAGE_VIEW_V18 --}}
                <div
                    id="core-email-message-view-panel"
                    class="hidden"
                >
                    <div class="rounded-xl border border-slate-200 bg-white">

                        <div class="border-b border-slate-200 p-5">
                            <button
                                type="button"
                                id="core-email-message-back"
                                class="mb-4 inline-flex items-center gap-2 text-sm font-semibold text-blue-700 hover:text-blue-900"
                            >
                                ← Back to folder
                            </button>

                            <div class="flex flex-wrap items-start justify-between gap-4">
                                <div class="min-w-0 flex-1">
                                    <h2
                                        id="core-email-view-subject"
                                        class="text-xl font-bold text-slate-900"
                                    >
                                        Email
                                    </h2>

                                    <div class="mt-3 space-y-1 text-sm text-slate-600">
                                        <div>
                                            <span class="font-semibold text-slate-800">From:</span>
                                            <span id="core-email-view-from"></span>
                                        </div>

                                        <div>
                                            <span class="font-semibold text-slate-800">To:</span>
                                            <span id="core-email-view-to"></span>
                                        </div>

                                        <div
                                            id="core-email-view-cc-row"
                                            class="hidden"
                                        >
                                            <span class="font-semibold text-slate-800">CC:</span>
                                            <span id="core-email-view-cc"></span>
                                        </div>
                                    </div>
                                </div>

                                <div class="text-right">
                                    <div
                                        id="core-email-view-source"
                                        class="inline-flex rounded-full bg-blue-50 px-2.5 py-1 text-xs font-bold text-blue-700"
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

                        {{-- ESUBIZ_CORE_EMAIL_DETAIL_ACTIONS_V1 --}}
                        <div
                            id="core-email-message-actions"
                            class="hidden flex-wrap items-center gap-2 border-b border-slate-200 px-5 py-3"
                        >
                            <button type="button"
                                id="core-email-view-reply"
                                class="hidden rounded-lg bg-blue-600 px-3 py-2 text-sm font-semibold text-white hover:bg-blue-700">
                                Reply
                            </button>

                            <button type="button"
                                id="core-email-view-forward"
                                class="hidden rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                                Forward
                            </button>

                            <button type="button"
                                id="core-email-view-read"
                                data-detail-action="read"
                                class="hidden rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                                Mark Read
                            </button>

                            <button type="button"
                                id="core-email-view-unread"
                                data-detail-action="unread"
                                class="hidden rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                                Mark Unread
                            </button>

                            <button type="button"
                                id="core-email-view-move-spam"
                                data-detail-action="spam"
                                class="hidden rounded-lg border border-amber-300 bg-white px-3 py-2 text-sm font-semibold text-amber-700 hover:bg-amber-50">
                                Move to Spam
                            </button>

                            <button type="button"
                                id="core-email-view-move-inbox"
                                data-detail-action="inbox"
                                class="hidden rounded-lg border border-blue-300 bg-white px-3 py-2 text-sm font-semibold text-blue-700 hover:bg-blue-50">
                                Move to Inbox
                            </button>

                            <button type="button"
                                id="core-email-view-edit"
                                class="hidden rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                                Edit
                            </button>

                            <button type="button"
                                id="core-email-view-send-draft"
                                class="hidden rounded-lg bg-blue-600 px-3 py-2 text-sm font-semibold text-white hover:bg-blue-700">
                                Send
                            </button>

                            <button type="button"
                                id="core-email-view-restore"
                                data-detail-action="restore"
                                class="hidden rounded-lg border border-emerald-300 bg-white px-3 py-2 text-sm font-semibold text-emerald-700 hover:bg-emerald-50">
                                Restore
                            </button>

                            <button type="button"
                                id="core-email-view-delete"
                                data-detail-action="delete"
                                class="hidden rounded-lg border border-red-300 bg-white px-3 py-2 text-sm font-semibold text-red-700 hover:bg-red-50">
                                Delete
                            </button>

                            <button type="button"
                                id="core-email-view-delete-permanent"
                                data-detail-action="delete_permanently"
                                class="hidden rounded-lg bg-red-600 px-3 py-2 text-sm font-semibold text-white hover:bg-red-700">
                                Delete Permanently
                            </button>
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
                        {{-- ESUBIZ_CORE_EMAIL_CC_BCC_TOGGLE_V1 --}}
                        <div>
                            <div class="mb-1.5 flex items-center justify-between gap-3">
                                <label class="block text-sm font-semibold text-slate-700">
                                    To
                                </label>

                                <div class="flex items-center gap-2 text-xs font-semibold">
                                    <button
                                        id="core-email-compose-cc-toggle"
                                        type="button"
                                        class="rounded-md px-2 py-1 text-blue-700 transition hover:bg-blue-50"
                                        aria-expanded="false"
                                        aria-controls="core-email-compose-cc-wrap"
                                    >
                                        CC
                                    </button>

                                    <button
                                        id="core-email-compose-bcc-toggle"
                                        type="button"
                                        class="rounded-md px-2 py-1 text-blue-700 transition hover:bg-blue-50"
                                        aria-expanded="false"
                                        aria-controls="core-email-compose-bcc-wrap"
                                    >
                                        BCC
                                    </button>
                                </div>
                            </div>

                            <input
                                id="core-email-compose-to"
                                type="text"
                                placeholder="recipient@example.com"
                                class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm outline-none focus:border-slate-500"
                            >
                        </div>

                        <div
                            id="core-email-compose-cc-wrap"
                            class="hidden"
                        >
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

                        <div
                            id="core-email-compose-bcc-wrap"
                            class="hidden"
                        >
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

                                    <button
                                        type="button"
                                        data-compose-command="createLink"
                                        class="rounded px-2.5 py-1.5 text-sm font-semibold text-slate-700 hover:bg-white"
                                        title="Add link"
                                        aria-label="Add link"
                                    >
                                        Link
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
                        {{-- ESUBIZ_CORE_EMAIL_ATTACHMENT_PREVIEW_V2 --}}
                        <div
                            id="core-email-compose-attachment-previews"
                            class="mt-3 hidden"
                        >
                            <div
                                id="core-email-compose-attachment-preview-list"
                                class="flex flex-wrap gap-2"
                            ></div>
                        </div>


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
                        {{-- ESUBIZ_CORE_EMAIL_SETTINGS_TWO_CARD_GRID_V4 --}}
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                        <section class="rounded-xl border border-slate-200 p-5">
                            <h3 class="font-bold text-slate-900">
                                Email Account
                            </h3>

                            <div
class="mt-4">
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

                                {{-- ESUBIZ_CORE_EMAIL_NO_MAILBOX_PASSWORD_V1 --}}
                                {{--
                                    Core Email uses the authenticated Core session.
                                    No separate mailbox password is exposed here.
                                --}}
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

                        </div>
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
                                                type="text"
                                                placeholder="e.g. esuvex.com"
                                                class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100"
                                            
                                                inputmode="url">
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

                                    <p class="mt-3 text-xs leading-5 text-slate-500">
                                        Enter only your username or handle. Example: <strong>esuvex</strong>.
                                        Esubiz will create the complete social media link automatically.
                                    </p>

                                    <div
                                        id="core-email-footer-social-fields"
                                        class="mt-4 grid gap-4 md:grid-cols-2"
                                    >
                                        @foreach([
                                            'facebook' => [
                                                'label' => 'Facebook',
                                                'placeholder' => 'e.g. esuvex',
                                            ],
                                            'instagram' => [
                                                'label' => 'Instagram',
                                                'placeholder' => 'e.g. esuvex',
                                            ],
                                            'linkedin' => [
                                                'label' => 'LinkedIn',
                                                'placeholder' => 'e.g. esuvex',
                                            ],
                                            'x' => [
                                                'label' => 'X (Twitter)',
                                                'placeholder' => 'e.g. esuvex',
                                            ],
                                            'youtube' => [
                                                'label' => 'YouTube',
                                                'placeholder' => 'e.g. esuvex',
                                            ],
                                            'tiktok' => [
                                                'label' => 'TikTok',
                                                'placeholder' => 'e.g. esuvex',
                                            ],
                                        ] as $socialKey => $social)
                                            <div>
                                                <label
                                                    for="core-email-footer-social-{{ $socialKey }}"
                                                    class="mb-1.5 block text-xs font-semibold text-slate-600"
                                                >
                                                    {{ $social['label'] }}
                                                </label>

                                                <input
                                                    id="core-email-footer-social-{{ $socialKey }}"
                                                    type="text"
                                                    inputmode="url"
                                                    placeholder="{{ $social['placeholder'] }}"
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

                            <div class="mt-5 grid gap-5 sm:grid-cols-2 lg:grid-cols-5">

                                <div>
                                    <label
                                        for="core-email-branding-background"
                                        class="mb-1.5 block text-sm font-semibold text-slate-700"
                                    >
                                        Email Background
                                    </label>

                                    <div class="flex items-center gap-2">
                                        <input
                                            id="core-email-branding-background"
                                            type="color"
                                            value="#f8fafc"
                                            title="Choose Email Background"
                                            class="h-11 w-14 shrink-0 cursor-pointer rounded-lg border border-slate-300 bg-white p-1"
                                        >

                                        <input
                                            id="core-email-branding-background-hex"
                                            type="text"
                                            value="#F8FAFC"
                                            maxlength="7"
                                            spellcheck="false"
                                            aria-label="Email Background color code"
                                            class="h-11 w-24 rounded-lg border border-slate-300 bg-white px-2 text-sm font-semibold text-slate-700 outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100"
                                        >


                                    </div>
                                </div>

                                <div>
                                    <label
                                        for="core-email-branding-accent"
                                        class="mb-1.5 block text-sm font-semibold text-slate-700"
                                    >
                                        Brand Color
                                    </label>

                                    <div class="flex items-center gap-2">
                                        <input
                                            id="core-email-branding-accent"
                                            type="color"
                                            value="#0f172a"
                                            title="Choose Brand Color"
                                            class="h-11 w-14 shrink-0 cursor-pointer rounded-lg border border-slate-300 bg-white p-1"
                                        >

                                        <input
                                            id="core-email-branding-accent-hex"
                                            type="text"
                                            value="#0F172A"
                                            maxlength="7"
                                            spellcheck="false"
                                            aria-label="Brand Color color code"
                                            class="h-11 w-24 rounded-lg border border-slate-300 bg-white px-2 text-sm font-semibold text-slate-700 outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100"
                                        >


                                    </div>
                                </div>

                                <div>
                                    <label
                                        for="core-email-branding-text-color"
                                        class="mb-1.5 block text-sm font-semibold text-slate-700"
                                    >
                                        Text Color
                                    </label>

                                    <div class="flex items-center gap-2">
                                        <input
                                            id="core-email-branding-text-color"
                                            type="color"
                                            value="#334155"
                                            title="Choose Text Color"
                                            class="h-11 w-14 shrink-0 cursor-pointer rounded-lg border border-slate-300 bg-white p-1"
                                        >

                                        <input
                                            id="core-email-branding-text-color-hex"
                                            type="text"
                                            value="#334155"
                                            maxlength="7"
                                            spellcheck="false"
                                            aria-label="Text Color color code"
                                            class="h-11 w-24 rounded-lg border border-slate-300 bg-white px-2 text-sm font-semibold text-slate-700 outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100"
                                        >


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

{{-- ESUBIZ_CORE_EMAIL_LEVEL1_NAV_MERGED_V7 --}}


{{-- ESUBIZ_CORE_EMAIL_BRANDING_NAV_MERGED_V5:
     Branding navigation is owned by authoritative activateSection(). --}}

{{-- ESUBIZ_CORE_EMAIL_BRANDING_RUNTIME_V1 --}}
<script>
document.addEventListener('DOMContentLoaded', () => {
    const saveButton = document.getElementById('core-email-save-branding');

    if (!saveButton) {
        return;
    }

    const settings = @json($coreEmailSiteSettings ?? []);
    const saveUrl = @json(route('core.email.branding.save', [
        'subdomain' => request()->route('subdomain'),
    ]));

    const el = id => document.getElementById(id);

    const value = (id, key, fallback = '') => {
        const node = el(id);
        if (!node) return;

        node.value =
            settings[key] !== undefined &&
            settings[key] !== null
                ? settings[key]
                : fallback;
    };

    const checked = (id, key, fallback = true) => {
        const node = el(id);
        if (!node) return;

        if (settings[key] === undefined) {
            node.checked = fallback;
            return;
        }

        node.checked =
            String(settings[key]) === '1' ||
            settings[key] === true;
    };

    value(
        'core-email-branding-header',
        'email_branding_header',
        'Thank you for connecting with us. We’re pleased to keep you updated.'
    );

    value(
        'core-email-branding-footer',
        'email_branding_footer',
        'Thank you for choosing us. We value your trust and look forward to serving you.'
    );

    value(
        'core-email-branding-signature',
        'email_branding_signature',
        'Warm regards,\nThe Team'
    );

    checked(
        'core-email-footer-contact-enabled',
        'email_branding_contact_enabled'
    );

    value('core-email-footer-phone', 'email_branding_contact_phone');
    value('core-email-footer-email', 'email_branding_contact_email');
    value('core-email-footer-website', 'email_branding_contact_website');
    value('core-email-footer-address', 'email_branding_contact_address');

    checked(
        'core-email-footer-social-enabled',
        'email_branding_social_enabled'
    );

    ['facebook', 'instagram', 'linkedin', 'x', 'youtube', 'tiktok']
        .forEach(network => {
            value(
                `core-email-footer-social-${network}`,
                `email_branding_social_${network}`
            );
        });

    checked(
        'core-email-footer-unsubscribe-enabled',
        'email_branding_unsubscribe_enabled'
    );

    value(
        'core-email-footer-unsubscribe-text',
        'email_branding_unsubscribe_text',
        'Unsubscribe from these emails'
    );

    value(
        'core-email-branding-background',
        'email_branding_background',
        '#f8fafc'
    );

    value(
        'core-email-branding-accent',
        'email_branding_accent',
        '#0f172a'
    );

    value(
        'core-email-branding-text-color',
        'email_branding_text_color',
        '#334155'
    );

    value(
        'core-email-branding-width',
        'email_branding_width',
        '640'
    );

    value(
        'core-email-branding-font',
        'email_branding_font',
        'system'
    );

    const contactEnabled = el('core-email-footer-contact-enabled');
    const socialEnabled = el('core-email-footer-social-enabled');
    const unsubscribeEnabled = el('core-email-footer-unsubscribe-enabled');

    const syncVisibility = () => {
        el('core-email-footer-contact-fields')
            ?.classList.toggle('hidden', !contactEnabled?.checked);

        el('core-email-footer-social-fields')
            ?.classList.toggle('hidden', !socialEnabled?.checked);

        el('core-email-footer-unsubscribe-fields')
            ?.classList.toggle('hidden', !unsubscribeEnabled?.checked);
    };

    [contactEnabled, socialEnabled, unsubscribeEnabled]
        .forEach(node => node?.addEventListener('change', syncVisibility));

    syncVisibility();

    saveButton.addEventListener('click', async () => {
        const form = new FormData();

        form.append(
            '_token',
            document.querySelector('meta[name="csrf-token"]')
                ?.getAttribute('content') || ''
        );

        form.append('header', el('core-email-branding-header')?.value || '');
        form.append('footer', el('core-email-branding-footer')?.value || '');
        form.append('signature', el('core-email-branding-signature')?.value || '');

        form.append('contact_enabled', contactEnabled?.checked ? '1' : '0');
        form.append('contact_phone', el('core-email-footer-phone')?.value || '');
        form.append('contact_email', el('core-email-footer-email')?.value || '');
        form.append('contact_website', el('core-email-footer-website')?.value || '');
        form.append('contact_address', el('core-email-footer-address')?.value || '');

        form.append('social_enabled', socialEnabled?.checked ? '1' : '0');

        ['facebook', 'instagram', 'linkedin', 'x', 'youtube', 'tiktok']
            .forEach(network => {
                form.append(
                    `social_${network}`,
                    el(`core-email-footer-social-${network}`)?.value || ''
                );
            });

        form.append(
            'unsubscribe_enabled',
            unsubscribeEnabled?.checked ? '1' : '0'
        );

        form.append(
            'unsubscribe_text',
            el('core-email-footer-unsubscribe-text')?.value || ''
        );

        form.append(
            'background',
            el('core-email-branding-background')?.value || '#f8fafc'
        );

        form.append(
            'accent',
            el('core-email-branding-accent')?.value || '#0f172a'
        );

        form.append(
            'text_color',
            el('core-email-branding-text-color')?.value || '#334155'
        );

        form.append(
            'width',
            el('core-email-branding-width')?.value || '640'
        );

        form.append(
            'font',
            el('core-email-branding-font')?.value || 'system'
        );

        const logo = el('core-email-branding-logo')?.files?.[0];

        if (logo) {
            form.append('logo', logo);
        }

        const originalText = saveButton.textContent;

        saveButton.disabled = true;
        saveButton.textContent = 'Saving...';

        try {
            const response = await fetch(saveUrl, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: form,
            });

            const data = await response.json();

            if (!response.ok) {
                throw new Error(
                    data.message ||
                    Object.values(data.errors || {}).flat()[0] ||
                    'Unable to save email branding.'
                );
            }

            saveButton.textContent = 'Saved';

            setTimeout(() => {
                saveButton.textContent = originalText;
            }, 1500);
        } catch (error) {
            alert(error.message || 'Unable to save email branding.');
            saveButton.textContent = originalText;
        } finally {
            saveButton.disabled = false;
        }
    });
});
</script>

{{-- ESUBIZ_CORE_EMAIL_BRANDING_PREVIEW_RUNTIME_V1 --}}
<script>
document.addEventListener('DOMContentLoaded', () => {
    const previewButton =
        document.querySelector('[data-core-email-branding-preview]');

    const modal =
        document.getElementById('core-email-branding-preview-modal');

    if (!previewButton || !modal) {
        return;
    }

    const el = id => document.getElementById(id);

    const closeButton =
        el('core-email-branding-preview-close');

    const card =
        el('core-email-branding-preview-card');

    const stage =
        el('core-email-branding-preview-stage');

    const previewLogo =
        el('core-email-branding-preview-logo');

    const previewHeader =
        el('core-email-branding-preview-header');

    const previewSignature =
        el('core-email-branding-preview-signature');

    const previewFooter =
        el('core-email-branding-preview-footer');

    const previewContact =
        el('core-email-branding-preview-contact');

    const previewSocial =
        el('core-email-branding-preview-social');

    const previewUnsubscribe =
        el('core-email-branding-preview-unsubscribe');

    const websiteName = @json($coreEmailWebsiteName ?? 'Website');
    const websiteLogo = @json($coreEmailWebsiteLogoUrl ?? null);

    let uploadedLogoUrl = null;

    const logoInput =
        el('core-email-branding-logo');

    logoInput?.addEventListener('change', () => {
        if (uploadedLogoUrl) {
            URL.revokeObjectURL(uploadedLogoUrl);
            uploadedLogoUrl = null;
        }

        const file = logoInput.files?.[0];

        if (file) {
            uploadedLogoUrl = URL.createObjectURL(file);
        }
    });

    const escapeHtml = value => {
        const div = document.createElement('div');
        div.textContent = value ?? '';
        return div.innerHTML;
    };

    const renderLogo = () => {
        const src = uploadedLogoUrl || websiteLogo;

        if (src) {
            previewLogo.innerHTML = `
                <img
                    src="${escapeHtml(src)}"
                    alt="${escapeHtml(websiteName)} logo"
                    class="mx-auto max-h-16 max-w-[220px] object-contain"
                >
            `;
            return;
        }

        previewLogo.textContent = websiteName;
    };

    const renderContact = () => {
        const enabled =
            el('core-email-footer-contact-enabled')?.checked;

        if (!enabled) {
            previewContact.classList.add('hidden');
            previewContact.innerHTML = '';
            return;
        }

        const items = [
            el('core-email-footer-phone')?.value?.trim(),
            el('core-email-footer-email')?.value?.trim(),
            el('core-email-footer-website')?.value?.trim(),
            el('core-email-footer-address')?.value?.trim(),
        ].filter(Boolean);

        if (!items.length) {
            previewContact.classList.add('hidden');
            previewContact.innerHTML = '';
            return;
        }

        previewContact.innerHTML =
            items.map(escapeHtml).join(' &nbsp; • &nbsp; ');

        previewContact.classList.remove('hidden');
    };

    const renderSocial = () => {
        const enabled =
            el('core-email-footer-social-enabled')?.checked;

        if (!enabled) {
            previewSocial.classList.add('hidden');
            previewSocial.innerHTML = '';
            return;
        }

        const socials = {
            facebook: {
                label: 'Facebook',
                color: '#1877F2',
                svg: `<svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true"><path fill="currentColor" d="M24 12.073C24 5.405 18.627 0 12 0S0 5.405 0 12.073C0 18.1 4.388 23.094 10.125 24v-8.437H7.078v-3.49h3.047V9.414c0-3.025 1.792-4.697 4.533-4.697 1.312 0 2.686.236 2.686.236v2.971h-1.513c-1.49 0-1.956.931-1.956 1.887v2.262h3.328l-.532 3.49h-2.796V24C19.612 23.094 24 18.1 24 12.073z"/></svg>`
            },
            instagram: {
                label: 'Instagram',
                color: '#E4405F',
                svg: `<svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true"><path fill="currentColor" d="M7.75 2h8.5A5.76 5.76 0 0 1 22 7.75v8.5A5.76 5.76 0 0 1 16.25 22h-8.5A5.76 5.76 0 0 1 2 16.25v-8.5A5.76 5.76 0 0 1 7.75 2zm0 2A3.75 3.75 0 0 0 4 7.75v8.5A3.75 3.75 0 0 0 7.75 20h8.5A3.75 3.75 0 0 0 20 16.25v-8.5A3.75 3.75 0 0 0 16.25 4h-8.5z"/><path fill="currentColor" d="M12 7a5 5 0 1 1 0 10 5 5 0 0 1 0-10zm0 2a3 3 0 1 0 0 6 3 3 0 0 0 0-6zm5.5-3.25a1.25 1.25 0 1 1 0 2.5 1.25 1.25 0 0 1 0-2.5z"/></svg>`
            },
            linkedin: {
                label: 'LinkedIn',
                color: '#0A66C2',
                svg: `<svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true"><path fill="currentColor" d="M20.45 20.45h-3.56v-5.57c0-1.33-.03-3.04-1.85-3.04-1.85 0-2.14 1.45-2.14 2.94v5.67H9.34V8.98h3.42v1.57h.05c.48-.9 1.64-1.85 3.37-1.85 3.6 0 4.27 2.37 4.27 5.46v6.29zM5.32 7.41a2.07 2.07 0 1 1 0-4.14 2.07 2.07 0 0 1 0 4.14zm1.78 13.04H3.54V8.98H7.1v11.47z"/></svg>`
            },
            x: {
                label: 'X',
                color: '#000000',
                svg: `<svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true"><path fill="currentColor" d="M18.244 2H21.552l-7.227 8.26L22.827 22h-6.657l-5.214-6.817L4.99 22H1.68l7.73-8.835L1.254 2h6.826l4.713 6.231L18.244 2zm-1.161 17.93h1.833L7.084 3.966H5.117L17.083 19.93z"/></svg>`
            },
            youtube: {
                label: 'YouTube',
                color: '#FF0000',
                svg: `<svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true"><path fill="currentColor" d="M23.5 6.19a3.02 3.02 0 0 0-2.12-2.14C19.5 3.54 12 3.54 12 3.54s-7.5 0-9.38.51A3.02 3.02 0 0 0 .5 6.19 31.5 31.5 0 0 0 0 12a31.5 31.5 0 0 0 .5 5.81 3.02 3.02 0 0 0 2.12 2.14c1.88.51 9.38.51 9.38.51s7.5 0 9.38-.51a3.02 3.02 0 0 0 2.12-2.14A31.5 31.5 0 0 0 24 12a31.5 31.5 0 0 0-.5-5.81zM9.55 15.57V8.43L15.82 12l-6.27 3.57z"/></svg>`
            },
            tiktok: {
                label: 'TikTok',
                color: '#000000',
                svg: `<svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true"><path fill="currentColor" d="M16.6 2c.2 1.7 1.15 3.22 2.6 4.13A7.1 7.1 0 0 0 23 7.2v3.67a10.7 10.7 0 0 1-6.35-2.08v7.02A6.2 6.2 0 1 1 11.3 9.67v3.72a2.58 2.58 0 1 0 1.72 2.42V2h3.58z"/></svg>`
            },
        };

        const links = Object.entries(socials)
            .map(([key, social]) => {
                const url =
                    el(`core-email-footer-social-${key}`)
                        ?.value?.trim();

                if (!url) {
                    return '';
                }

                return `
                    <a
                        href="${escapeHtml(url)}"
                        target="_blank"
                        rel="noopener noreferrer"
                        title="${escapeHtml(social.label)}"
                        aria-label="${escapeHtml(social.label)}"
                        style="
                            display:inline-flex;
                            width:34px;
                            height:34px;
                            align-items:center;
                            justify-content:center;
                            border-radius:9999px;
                            background:${social.color};
                            color:#ffffff;
                            font-family:Arial,sans-serif;
                            font-size:13px;
                            font-weight:700;
                            text-decoration:none;
                        "
                    ><span style="display:block;width:18px;height:18px;color:#ffffff;line-height:0;">${social.svg}</span></a>
                `;
            })
            .filter(Boolean);

        if (!links.length) {
            previewSocial.classList.add('hidden');
            previewSocial.innerHTML = '';
            return;
        }

        previewSocial.innerHTML = links.join('');
        previewSocial.classList.remove('hidden');
        previewSocial.classList.add('flex');
    };

    const renderUnsubscribe = () => {
        const enabled =
            el('core-email-footer-unsubscribe-enabled')?.checked;

        if (!enabled) {
            previewUnsubscribe.classList.add('hidden');
            return;
        }

        const text =
            el('core-email-footer-unsubscribe-text')
                ?.value?.trim()
            || 'Unsubscribe from these emails';

        const link = previewUnsubscribe.querySelector('a');

        if (link) {
            link.textContent = text;
        }

        previewUnsubscribe.classList.remove('hidden');
    };

    const bindBrandingColor = (pickerId, hexId) => {
        const picker = el(pickerId);
        const hex = el(hexId);

        if (!picker || !hex) {
            return;
        }

        hex.value = picker.value.toUpperCase();

        picker.addEventListener('input', () => {
            hex.value = picker.value.toUpperCase();
        });

        hex.addEventListener('input', () => {
            let value = hex.value.trim();

            if (value && !value.startsWith('#')) {
                value = '#' + value;
            }

            if (/^#[0-9A-Fa-f]{6}$/.test(value)) {
                picker.value = value;
            }
        });

        hex.addEventListener('blur', () => {
            let value = hex.value.trim();

            if (value && !value.startsWith('#')) {
                value = '#' + value;
            }

            if (/^#[0-9A-Fa-f]{6}$/.test(value)) {
                picker.value = value;
                hex.value = value.toUpperCase();
            } else {
                hex.value = picker.value.toUpperCase();
            }
        });
    };

    bindBrandingColor(
        'core-email-branding-background',
        'core-email-branding-background-hex'
    );

    bindBrandingColor(
        'core-email-branding-accent',
        'core-email-branding-accent-hex'
    );

    bindBrandingColor(
        'core-email-branding-text-color',
        'core-email-branding-text-color-hex'
    );

    const renderPreview = () => {
        renderLogo();

        previewHeader.textContent =
            el('core-email-branding-header')?.value?.trim() || '';

        previewSignature.textContent =
            el('core-email-branding-signature')?.value || '';

        previewFooter.textContent =
            el('core-email-branding-footer')?.value?.trim() || '';

        renderContact();
        renderSocial();
        renderUnsubscribe();

        const background =
            el('core-email-branding-background')?.value
            || '#f8fafc';

        const accent =
            el('core-email-branding-accent')?.value
            || '#0f172a';

        const textColor =
            el('core-email-branding-text-color')?.value
            || '#334155';

        const width =
            el('core-email-branding-width')?.value
            || '640';

        const font =
            el('core-email-branding-font')?.value
            || 'system';

        stage.style.backgroundColor = background;
        card.style.maxWidth = `${width}px`;

        const fontFamilies = {
            system: 'Arial, Helvetica, sans-serif',
            serif: 'Georgia, Times New Roman, serif',
            clean: 'Helvetica, Arial, sans-serif',
        };

        card.style.fontFamily =
            fontFamilies[font] || fontFamilies.system;

        previewLogo.style.color = accent;
        card.style.color = textColor;

        card.querySelectorAll('p').forEach(node => {
            node.style.color = textColor;
        });

        if (previewHeader) {
            previewHeader.style.color = accent;
        }

        const unsubscribeLink =
            previewUnsubscribe?.querySelector('a');

        if (unsubscribeLink) {
            unsubscribeLink.style.color = accent;
        }
    };

    const openPreview = () => {
        renderPreview();

        modal.classList.remove('hidden');
        modal.classList.add('flex');

        document.body.style.overflow = 'hidden';
    };

    const closePreview = () => {
        modal.classList.add('hidden');
        modal.classList.remove('flex');

        document.body.style.overflow = '';
    };

    previewButton.addEventListener('click', openPreview);
    closeButton?.addEventListener('click', closePreview);

    modal.addEventListener('click', event => {
        if (event.target === modal) {
            closePreview();
        }
    });

    document.addEventListener('keydown', event => {
        if (
            event.key === 'Escape' &&
            !modal.classList.contains('hidden')
        ) {
            closePreview();
        }
    });
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

    /*
     * ESUBIZ_CORE_EMAIL_AUTHORITATIVE_LEVEL2_V5
     *
     * All Level-2 workspaces are controlled by activateSection().
     * Exactly one content workspace is visible at a time.
     */
    const brandingPanel =
        document.getElementById('core-email-branding-panel');

    const messageViewPanel =
        document.getElementById('core-email-message-view-panel');

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

    /*
     * ESUBIZ_CORE_EMAIL_V17_ACTION_HELPERS_V1
     */
    function v17ActionsForFolder() {
        if (folder === 'inbox') {
            return [
                ['read', 'Mark read'],
                ['unread', 'Mark unread'],
                ['spam', 'Move to Spam'],
                ['delete', 'Delete'],
            ];
        }

        if (folder === 'spam') {
            return [
                ['read', 'Mark read'],
                ['unread', 'Mark unread'],
                ['inbox', 'Move to Inbox'],
                ['delete', 'Delete'],
            ];
        }

        if (folder === 'draft') {
            return [
                ['edit', 'Edit'],
                ['send', 'Send'],
                ['delete', 'Delete'],
            ];
        }

        if (folder === 'sent') {
            return [
                ['delete', 'Delete'],
            ];
        }

        if (folder === 'trash') {
            return [
                ['restore', 'Restore'],
                [
                    'delete_permanently',
                    'Delete permanently'
                ],
            ];
        }

        return [];
    }

    function v17ServerActions() {
        return v17ActionsForFolder()
            .filter(([action]) =>
                !['edit', 'send'].includes(action)
            );
    }

    function v17ActionMenu(id) {
        return v17ActionsForFolder()
            .map(([action, label]) => `
                <button
                    type="button"
                    data-v17-action="${escapeEmailHtml(action)}"
                    data-message-id="${id}"
                    class="block w-full px-4 py-2.5 text-left text-sm font-medium text-slate-700 hover:bg-slate-50"
                >
                    ${escapeEmailHtml(label)}
                </button>
            `)
            .join('');
    }

    function v17ActionUrl(id) {
        const template =
            root.dataset.messageActionUrlTemplate;

        if (!template) {
            return '';
        }

        const token =
            '/messages/0/action';

        if (!template.includes(token)) {
            return '';
        }

        return template.replace(
            token,
            `/messages/${id}/action`
        );
    }

    async function performV17MessageAction(
        id,
        action
    ) {
        const url =
            v17ActionUrl(id);

        if (!url) {
            throw new Error(
                'Message action URL unavailable.'
            );
        }

        if (
            action === 'delete_permanently'
            && !window.confirm(
                'Permanently delete this email? This cannot be undone.'
            )
        ) {
            return false;
        }

        const response =
            await fetch(
                url,
                {
                    method: 'POST',
                    headers: {
                        'X-Requested-With':
                            'XMLHttpRequest',
                        'Accept':
                            'application/json',
                        'Content-Type':
                            'application/json',
                        'X-CSRF-TOKEN':
                            csrfToken,
                    },
                    credentials:
                        'same-origin',
                    body:
                        JSON.stringify({
                            mailbox_id:
                                mailboxId,
                            action:
                                action,
                        }),
                }
            );

        const responseText =
            await response.text();

        let responsePayload = {};

        if (responseText) {
            try {
                responsePayload =
                    JSON.parse(responseText);
            } catch (error) {
                responsePayload = {};
            }
        }

        if (!response.ok) {
            const serverMessage =
                responsePayload.message
                || responseText
                || `HTTP ${response.status}`;

            throw new Error(
                `Message action failed (${response.status}): ${serverMessage}`
            );
        }

        if (responsePayload?.ok === false) {
            throw new Error(
                responsePayload.message
                || 'Message action was rejected.'
            );
        }

        await loadMessages();

        return true;
    }

    /*
     * ESUBIZ_CORE_EMAIL_V17_TABLE_RENDERER_V1
     *
     * One authoritative folder renderer.
     * No secondary navigation/runtime is introduced.
     */
    function escapeEmailHtml(value) {
        return String(value ?? '')
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#039;');
    }

    function folderCounterpartyHeading() {
        return folder === 'sent'
            ? 'To'
            : folder === 'draft'
                ? 'To'
                : 'From';
    }

    function formatFolderDate(value) {
        if (!value) {
            return '—';
        }

        const date =
            new Date(value);

        if (Number.isNaN(date.getTime())) {
            return String(value);
        }

        return date.toLocaleString();
    }

    function renderV17Messages(messages) {
        const rows =
            Array.isArray(messages)
                ? messages
                : [];

        if (!rows.length) {
            const emptyText =
                folder === 'inbox'
                    ? 'No emails in Inbox.'
                    : folder === 'sent'
                        ? 'No sent emails yet.'
                        : folder === 'draft'
                            ? 'No drafts yet.'
                            : folder === 'spam'
                                ? 'No spam emails.'
                                : 'Trash is empty.';

            results.innerHTML = `
                <div
                    class="rounded-xl border border-slate-200 bg-white py-12 text-center text-sm font-semibold text-slate-600"
                >
                    ${escapeEmailHtml(emptyText)}
                </div>
            `;

            return;
        }

        const body =
            rows.map(message => {
                const id =
                    Number(message.id);

                /*
                 * ESUBIZ_CORE_EMAIL_READ_STATE_V1
                 *
                 * Read/unread is meaningful for received mail.
                 * Unread Inbox/Spam rows are visually stronger.
                 */
                const receivedFolder =
                    folder === 'inbox'
                    || folder === 'spam';

                const isUnread =
                    receivedFolder
                    && !message.read_at;

                const counterpartyClass =
                    isUnread
                        ? 'font-bold text-slate-900'
                        : receivedFolder
                            ? 'font-normal text-slate-500'
                            : 'font-medium text-slate-800';

                const subjectClass =
                    isUnread
                        ? 'font-bold text-slate-900'
                        : receivedFolder
                            ? 'font-normal text-slate-500'
                            : 'font-medium text-slate-800';

                const dateClass =
                    isUnread
                        ? 'font-bold text-slate-800'
                        : 'font-normal text-slate-400';

                const rowReadClass =
                    isUnread
                        ? 'bg-white'
                        : receivedFolder
                            ? 'bg-slate-50/40'
                            : '';

                const attachment =
                    message.has_attachments
                        ? `
                            <span
                                class="ml-2 rounded bg-slate-100 px-2 py-1 text-[10px] font-semibold text-slate-600"
                            >
                                Attachment
                            </span>
                        `
                        : '';

                return `
                    <tr
                        data-v17-row
                        data-message-id="${id}"
                        class="core-email-v17-row ${rowReadClass} cursor-pointer border-b border-slate-100 transition hover:bg-blue-50/70"
                    >
                        <td
                            class="w-12 px-4 py-4"
                            data-v17-no-open
                        >
                            <input
                                type="checkbox"
                                data-v17-select="${id}"
                                class="h-4 w-4 rounded border-slate-300"
                            >
                        </td>

                        <td
                            class="px-4 py-4 ${counterpartyClass}"
                        >
                            ${escapeEmailHtml(
                                message.counterparty || '—'
                            )}
                        </td>

                        <td class="px-4 py-4">
                            <span
                                class="${subjectClass}"
                            >
                                ${escapeEmailHtml(
                                    message.subject
                                    || '(No subject)'
                                )}
                            </span>

                            ${attachment}
                        </td>

                        <td class="px-4 py-4">
                            <span
                                class="rounded-full bg-indigo-50 px-2.5 py-1 text-xs font-semibold text-indigo-700"
                            >
                                ${escapeEmailHtml(
                                    message.source_label
                                    || 'Direct Email'
                                )}
                            </span>
                        </td>

                        <td
                            class="whitespace-nowrap px-4 py-4 text-sm ${dateClass}"
                        >
                            ${escapeEmailHtml(
                                formatFolderDate(
                                    message.created_at
                                )
                            )}
                        </td>

                        <td
                            class="w-20 px-4 py-4 text-center"
                            data-v17-no-open
                        >
                            <div
                                class="relative inline-block"
                                data-v17-menu-wrap
                            >
                                <button
                                    type="button"
                                    data-v17-menu
                                    data-message-id="${id}"
                                    aria-label="Email actions"
                                    class="inline-flex h-8 w-8 items-center justify-center rounded-full text-lg font-bold text-slate-600 hover:bg-slate-100 hover:text-blue-700"
                                >
                                    ⋮
                                </button>

                                <div
                                    data-v17-menu-panel
                                    class="absolute right-0 z-50 mt-1 hidden min-w-44 overflow-hidden rounded-xl border border-slate-200 bg-white py-1 text-left shadow-xl"
                                >
                                    ${v17ActionMenu(id)}
                                </div>
                            </div>
                        </td>
                    </tr>
                `;
            }).join('');

        results.innerHTML = `
            <div
                data-v17-bulk
                class="hidden mb-3 flex-wrap items-center gap-2 rounded-xl border border-blue-100 bg-blue-50 px-3 py-2"
            >
                <strong
                    data-v17-count
                    class="mr-2 text-xs text-blue-800"
                >
                    0 selected
                </strong>

                ${v17ServerActions()
                    .map(([action, label]) => `
                        <button
                            type="button"
                            data-v17-bulk-action="${escapeEmailHtml(action)}"
                            class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-700 hover:border-blue-300 hover:text-blue-700"
                        >
                            ${escapeEmailHtml(label)}
                        </button>
                    `)
                    .join('')}
            </div>

            <div
                class="overflow-visible rounded-xl border border-slate-200 bg-white"
            >
                <div class="overflow-x-auto">
                    <table
                        class="min-w-full border-collapse text-left"
                    >
                        <thead
                            class="border-b border-slate-200 bg-slate-50"
                        >
                            <tr>
                                <th
                                    class="w-12 px-4 py-3"
                                >
                                    <input
                                        type="checkbox"
                                        data-v17-all
                                        class="h-4 w-4 rounded border-slate-300"
                                    >
                                </th>

                                <th
                                    class="px-4 py-3 text-xs font-bold uppercase text-slate-500"
                                >
                                    ${escapeEmailHtml(
                                        folderCounterpartyHeading()
                                    )}
                                </th>

                                <th
                                    class="px-4 py-3 text-xs font-bold uppercase text-slate-500"
                                >
                                    Subject
                                </th>

                                <th
                                    class="px-4 py-3 text-xs font-bold uppercase text-slate-500"
                                >
                                    Source
                                </th>

                                <th
                                    class="px-4 py-3 text-xs font-bold uppercase text-slate-500"
                                >
                                    Date
                                </th>

                                <th
                                    class="w-20 px-4 py-3 text-center text-xs font-bold uppercase text-slate-500"
                                >
                                    Action
                                </th>
                            </tr>
                        </thead>

                        <tbody>
                            ${body}
                        </tbody>
                    </table>
                </div>
            </div>
        `;
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
                cache: 'no-store',
            });

            if (!response.ok) {
                throw new Error(
                    'Email request failed.'
                );
            }

            const data =
                await response.json();

            if (Array.isArray(data.messages)) {
                renderV17Messages(
                    data.messages
                );
            } else {
                results.innerHTML =
                    data.html;
            }

            page =
                Number(data.page || 1);

            previous.disabled =
                !data.has_previous;

            next.disabled =
                !data.has_next;

            pageLabel.textContent =
                `Page ${page}`;
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
        const section =
            tab.dataset.emailSection;

        /*
         * ESUBIZ_CORE_EMAIL_EXCLUSIVE_LEVEL2_V5
         *
         * Paint every Level-2 tab blue/white.
         * Selected = darker blue.
         */
        sectionTabs.forEach(item => {
            item.classList.remove(
                'bg-blue-600',
                'bg-blue-700',
                'bg-blue-800',
                'bg-slate-900',
                'bg-white',
                'text-slate-600'
            );

            item.classList.add(
                item === tab
                    ? 'bg-blue-800'
                    : 'bg-blue-600',
                'text-white'
            );
        });

        /*
         * Start from a completely closed workspace.
         * The requested section below is the only panel reopened.
         */
        listPanel?.classList.add('hidden');
        composePanel?.classList.add('hidden');
        settingsPanel?.classList.add('hidden');
        brandingPanel?.classList.add('hidden');
        messageViewPanel?.classList.add('hidden');

        /*
         * ESUBIZ_CORE_EMAIL_LEVEL2_CLICK_REGRESSION_FIX_V6
         *
         * Navigation must not depend on draft-editor variables
         * declared later in this script. Draft loading already
         * enters Compose by clicking the Compose tab.
         */
        if (section === 'compose') {
            composePanel?.classList.remove(
                'hidden'
            );

            return;
        }

        if (section === 'settings') {
            settingsPanel?.classList.remove(
                'hidden'
            );

            /*
             * ESUBIZ_CORE_EMAIL_SETTINGS_TAB_LOAD_V1
             */
            loadCoreEmailSettings()
                .catch(error => {
                    window.alert(
                        error?.message
                        || 'Unable to load mailbox settings.'
                    );
                });

            return;
        }

        if (section === 'branding') {
            brandingPanel?.classList.remove(
                'hidden'
            );

            return;
        }

        /*
         * Only actual mail folders are allowed to reach
         * the message loader.
         */
        const mailFolders = [
            'inbox',
            'spam',
            'draft',
            'sent',
            'trash',
        ];

        if (!mailFolders.includes(section)) {
            return;
        }

        folder = section;
        page = 1;

        folderTitle.textContent =
            folderLabels[folder] || 'Inbox';

        listPanel?.classList.remove(
            'hidden'
        );

        loadMessages();
    }

    /*
     * ESUBIZ_CORE_EMAIL_V17_ACTION_RUNTIME_V1
     */
    const v17Selected =
        new Set();

    function updateV17Selection() {
        const selectedInputs =
            results.querySelectorAll(
                '[data-v17-select]:checked'
            );

        v17Selected.clear();

        selectedInputs.forEach(input => {
            v17Selected.add(
                Number(input.dataset.v17Select)
            );
        });

        const toolbar =
            results.querySelector(
                '[data-v17-bulk]'
            );

        const count =
            results.querySelector(
                '[data-v17-count]'
            );

        if (toolbar) {
            toolbar.classList.toggle(
                'hidden',
                v17Selected.size === 0
            );

            toolbar.classList.toggle(
                'flex',
                v17Selected.size > 0
            );
        }

        if (count) {
            count.textContent =
                `${v17Selected.size} selected`;
        }

        const all =
            results.querySelector(
                '[data-v17-all]'
            );

        const total =
            results.querySelectorAll(
                '[data-v17-select]'
            ).length;

        if (all) {
            all.checked =
                total > 0
                && v17Selected.size === total;

            all.indeterminate =
                v17Selected.size > 0
                && v17Selected.size < total;
        }
    }

    results.addEventListener(
        'change',
        event => {
            const select =
                event.target.closest(
                    '[data-v17-select]'
                );

            if (select) {
                updateV17Selection();
                return;
            }

            const all =
                event.target.closest(
                    '[data-v17-all]'
                );

            if (!all) {
                return;
            }

            results
                .querySelectorAll(
                    '[data-v17-select]'
                )
                .forEach(input => {
                    input.checked =
                        all.checked;
                });

            updateV17Selection();
        }
    );

    results.addEventListener(
        'click',
        async event => {
            const menuButton =
                event.target.closest(
                    '[data-v17-menu]'
                );

            if (menuButton) {
                event.preventDefault();
                event.stopPropagation();

                const panel =
                    menuButton
                        .closest(
                            '[data-v17-menu-wrap]'
                        )
                        ?.querySelector(
                            '[data-v17-menu-panel]'
                        );

                results
                    .querySelectorAll(
                        '[data-v17-menu-panel]'
                    )
                    .forEach(item => {
                        if (item !== panel) {
                            item.classList.add(
                                'hidden'
                            );
                        }
                    });

                panel?.classList.toggle(
                    'hidden'
                );

                return;
            }

            const actionButton =
                event.target.closest(
                    '[data-v17-action]'
                );

            if (actionButton) {
                event.preventDefault();
                event.stopPropagation();

                const id =
                    Number(
                        actionButton.dataset.messageId
                    );

                const action =
                    actionButton.dataset.v17Action;

                if (action === 'edit') {
                    const row =
                        results.querySelector(
                            `[data-v17-row][data-message-id="${id}"]`
                        );

                    row?.dispatchEvent(
                        new MouseEvent(
                            'click',
                            {
                                bubbles: true,
                            }
                        )
                    );

                    return;
                }

                if (action === 'send') {
                    const row =
                        results.querySelector(
                            `[data-v17-row][data-message-id="${id}"]`
                        );

                    row?.dispatchEvent(
                        new MouseEvent(
                            'click',
                            {
                                bubbles: true,
                            }
                        )
                    );

                    return;
                }

                try {
                    actionButton.disabled =
                        true;

                    await performV17MessageAction(
                        id,
                        action
                    );
                } catch (error) {
                    window.alert(
                        'Unable to complete this email action.'
                    );
                } finally {
                    actionButton.disabled =
                        false;
                }

                return;
            }

            const bulkButton =
                event.target.closest(
                    '[data-v17-bulk-action]'
                );

            if (bulkButton) {
                event.preventDefault();
                event.stopPropagation();

                const ids =
                    Array.from(
                        v17Selected
                    );

                const action =
                    bulkButton.dataset.v17BulkAction;

                if (!ids.length) {
                    return;
                }

                if (
                    action === 'delete_permanently'
                    && !window.confirm(
                        `Permanently delete ${ids.length} selected email(s)? This cannot be undone.`
                    )
                ) {
                    return;
                }

                bulkButton.disabled =
                    true;

                try {
                    for (const id of ids) {
                        await performV17MessageAction(
                            id,
                            action
                        );
                    }
                } catch (error) {
                    window.alert(
                        'Unable to complete the selected email action.'
                    );
                } finally {
                    bulkButton.disabled =
                        false;
                }

                return;
            }

            const row =
                event.target.closest(
                    '[data-v17-row]'
                );

            if (
                row
                && !event.target.closest(
                    '[data-v17-no-open]'
                )
            ) {
                const id =
                    Number(
                        row.dataset.messageId
                    );

                if (
                    typeof openMessage
                    === 'function'
                ) {
                    openMessage(id);
                }
            }
        }
    );

    document.addEventListener(
        'click',
        event => {
            if (
                event.target.closest(
                    '[data-v17-menu-wrap]'
                )
            ) {
                return;
            }

            results
                .querySelectorAll(
                    '[data-v17-menu-panel]'
                )
                .forEach(panel => {
                    panel.classList.add(
                        'hidden'
                    );
                });
        }
    );

    /*
     * ESUBIZ_CORE_EMAIL_FOLDER_REFRESH_V1
     */
    const folderRefreshButton =
        document.getElementById(
            'core-email-folder-refresh'
        );

    if (folderRefreshButton) {
        folderRefreshButton.addEventListener(
            'click',
            async () => {
                if (!mailboxId) {
                    return;
                }

                folderRefreshButton.disabled =
                    true;

                try {
                    const response =
                        await fetch(
                            root.dataset.refreshUrl,
                            {
                                method: 'POST',
                                headers: {
                                    'X-Requested-With':
                                        'XMLHttpRequest',
                                    'Accept':
                                        'application/json',
                                    'Content-Type':
                                        'application/json',
                                    'X-CSRF-TOKEN':
                                        csrfToken,
                                },
                                credentials:
                                    'same-origin',
                                body:
                                    JSON.stringify({
                                        mailbox_id:
                                            mailboxId,
                                        folder:
                                            folder,
                                    }),
                            }
                        );

                    if (!response.ok) {
                        throw new Error(
                            'Refresh failed.'
                        );
                    }

                    page = 1;

                    await loadMessages();
                } catch (error) {
                    window.alert(
                        'Unable to refresh this folder.'
                    );
                } finally {
                    folderRefreshButton.disabled =
                        false;
                }
            }
        );
    }

    /*
     * ESUBIZ_CORE_EMAIL_REAL_NAVIGATION_LISTENERS_V8B
     *
     * Existing Core Email runtime remains authoritative for
     * mailbox state, Level-2 sections and AJAX message loading.
     */
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
        root.dataset.activeDraftId = '';
        renderDraftAttachments([]);
    };

    const openDraftInCompose = message => {
        openingExistingDraft = true;
        activeDraftId =
            Number(message.id) || null;

        root.dataset.activeDraftId =
            activeDraftId
                ? String(activeDraftId)
                : '';

        if (composeTo) {
            composeTo.value =
                message.to || '';
        }

        if (composeCc) {
            composeCc.value =
                message.cc || '';

            setComposeRecipientVisible(
                'cc',
                Boolean(
                    String(message.cc || '')
                        .trim()
                )
            );
        }

        if (composeBcc) {
            composeBcc.value =
                message.bcc || '';

            setComposeRecipientVisible(
                'bcc',
                Boolean(
                    String(message.bcc || '')
                        .trim()
                )
            );
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

    /*
     * ESUBIZ_CORE_EMAIL_CC_BCC_RUNTIME_V1
     */
    const composeCcWrap =
        document.getElementById(
            'core-email-compose-cc-wrap'
        );

    const composeBccWrap =
        document.getElementById(
            'core-email-compose-bcc-wrap'
        );

    const composeCcToggle =
        document.getElementById(
            'core-email-compose-cc-toggle'
        );

    const composeBccToggle =
        document.getElementById(
            'core-email-compose-bcc-toggle'
        );

    const setComposeRecipientVisible =
        (kind, visible) => {
            const wrap =
                kind === 'cc'
                    ? composeCcWrap
                    : composeBccWrap;

            const toggle =
                kind === 'cc'
                    ? composeCcToggle
                    : composeBccToggle;

            if (!wrap || !toggle) {
                return;
            }

            wrap.classList.toggle(
                'hidden',
                !visible
            );

            toggle.setAttribute(
                'aria-expanded',
                visible
                    ? 'true'
                    : 'false'
            );

            toggle.classList.toggle(
                'bg-blue-50',
                visible
            );
        };

    composeCcToggle?.addEventListener(
        'click',
        () => {
            const visible =
                composeCcWrap
                    ?.classList
                    .contains('hidden');

            setComposeRecipientVisible(
                'cc',
                Boolean(visible)
            );

            if (visible) {
                composeCc?.focus();
            }
        }
    );

    composeBccToggle?.addEventListener(
        'click',
        () => {
            const visible =
                composeBccWrap
                    ?.classList
                    .contains('hidden');

            setComposeRecipientVisible(
                'bcc',
                Boolean(visible)
            );

            if (visible) {
                composeBcc?.focus();
            }
        }
    );

    const composeSubject =
        document.getElementById(
            'core-email-compose-subject'
        );

    const composeBody =
        document.getElementById(
            'core-email-compose-body'
        );

    /*
     * ESUBIZ_CORE_EMAIL_COMPOSE_FORMATTING_V1
     *
     * The contenteditable body remains the canonical rich
     * compose surface. body_html is already persisted/sent.
     */
    const composeFormatButtons =
        Array.from(
            document.querySelectorAll(
                '[data-compose-command]'
            )
        );

    const normalizeComposeLink =
        value => {
            const raw =
                String(value || '').trim();

            if (!raw) {
                return '';
            }

            if (
                /^(https?:\/\/|mailto:|tel:)/i
                    .test(raw)
            ) {
                return raw;
            }

            return `https://${raw}`;
        };

    const runComposeFormat =
        (command, value = null) => {
            if (!composeBody) {
                return;
            }

            composeBody.focus();

            document.execCommand(
                command,
                false,
                value
            );

            composeBody.dispatchEvent(
                new Event(
                    'input',
                    {
                        bubbles: true
                    }
                )
            );
        };

    composeFormatButtons.forEach(
        button => {
            /*
             * Prevent the contenteditable selection from
             * disappearing before the command executes.
             */
            button.addEventListener(
                'mousedown',
                event => {
                    event.preventDefault();
                }
            );

            button.addEventListener(
                'click',
                () => {
                    const command =
                        button.dataset
                            .composeCommand
                        || '';

                    if (!command) {
                        return;
                    }

                    /*
                     * ESUBIZ_CORE_EMAIL_COMPOSE_LIST_FIX_V1
                     *
                     * Build the list directly from the current
                     * selection instead of relying on the browser's
                     * inconsistent insertUnorderedList implementation.
                     */
                    if (
                        command ===
                        'insertUnorderedList'
                    ) {
                        const selection =
                            window.getSelection();

                        if (
                            !selection
                            || selection.rangeCount < 1
                        ) {
                            return;
                        }

                        const range =
                            selection.getRangeAt(0);

                        if (
                            !composeBody.contains(
                                range.commonAncestorContainer
                            )
                        ) {
                            composeBody.focus();
                            return;
                        }

                        const selectedText =
                            selection.toString();

                        const lines =
                            String(selectedText || '')
                                .split(/\r?\n/)
                                .map(line => line.trim())
                                .filter(Boolean);

                        const list =
                            document.createElement(
                                'ul'
                            );

                        list.style.listStyleType =
                            'disc';

                        list.style.paddingLeft =
                            '1.5rem';

                        list.style.margin =
                            '0.5rem 0';

                        (
                            lines.length
                                ? lines
                                : ['']
                        ).forEach(line => {
                            const item =
                                document.createElement(
                                    'li'
                                );

                            if (line) {
                                item.textContent =
                                    line;
                            } else {
                                item.appendChild(
                                    document.createElement(
                                        'br'
                                    )
                                );
                            }

                            list.appendChild(item);
                        });

                        range.deleteContents();
                        range.insertNode(list);

                        const lastItem =
                            list.lastElementChild;

                        if (lastItem) {
                            const caret =
                                document.createRange();

                            caret.selectNodeContents(
                                lastItem
                            );

                            caret.collapse(false);

                            selection.removeAllRanges();
                            selection.addRange(caret);
                        }

                        composeBody.focus();

                        composeBody.dispatchEvent(
                            new Event(
                                'input',
                                {
                                    bubbles: true
                                }
                            )
                        );

                        return;
                    }

                    if (
                        command ===
                        'createLink'
                    ) {
                        const selection =
                            window.getSelection();

                        if (
                            !selection
                            || selection.rangeCount < 1
                            || selection.isCollapsed
                        ) {
                            window.alert(
                                'Select the text you want to link first.'
                            );

                            return;
                        }

                        const url =
                            window.prompt(
                                'Enter the link URL:'
                            );

                        if (url === null) {
                            return;
                        }

                        const normalized =
                            normalizeComposeLink(
                                url
                            );

                        if (!normalized) {
                            return;
                        }

                        runComposeFormat(
                            'createLink',
                            normalized
                        );

                        return;
                    }

                    runComposeFormat(
                        command
                    );
                }
            );
        }
    );

    /*
     * ESUBIZ_CORE_EMAIL_SETTINGS_RUNTIME_V1
     */
    const coreEmailSaveSettings =
        document.getElementById(
            'core-email-save-settings'
        );

    const coreEmailSettingsEmail =
        document.getElementById(
            'core-email-settings-email'
        );

    const coreEmailSettingsAddress =
        document.getElementById(
            'core-email-settings-address'
        );

    /*
     * ESUBIZ_CORE_EMAIL_FORWARD_SETTINGS_RUNTIME_V1
     */
    const coreEmailForwardAddress =
        document.getElementById(
            'core-email-forward-address'
        );

    const coreEmailRetentionFields =
        Array.from(
            document.querySelectorAll(
                '[data-retention-folder]'
            )
        );

    const coreEmailSourceFields =
        Array.from(
            document.querySelectorAll(
                '[data-email-source]'
            )
        );

    const coreEmailSettingsShowUrl =
        root.dataset.settingsShowUrl || '';

    const coreEmailSettingsSaveUrl =
        root.dataset.settingsSaveUrl || '';

    const loadCoreEmailSettings =
        async () => {
            if (
                !mailboxId
                || !coreEmailSettingsShowUrl
            ) {
                return;
            }

            const url =
                new URL(
                    coreEmailSettingsShowUrl,
                    window.location.origin
                );

            url.searchParams.set(
                'mailbox_id',
                mailboxId
            );

            const response =
                await fetch(
                    url.toString(),
                    {
                        headers: {
                            'X-Requested-With':
                                'XMLHttpRequest',
                            'Accept':
                                'application/json'
                        }
                    }
                );

            const payload =
                await response.json();

            if (!response.ok || !payload.ok) {
                throw new Error(
                    payload.message
                    || 'Unable to load mailbox settings.'
                );
            }

            if (coreEmailSettingsEmail) {
                coreEmailSettingsEmail.value =
                    payload.mailbox?.email || '';
            }

            if (coreEmailSettingsAddress) {
                coreEmailSettingsAddress.textContent =
                    payload.mailbox?.email || '';
            }

            if (coreEmailForwardAddress) {
                coreEmailForwardAddress.value =
                    payload.forward_address || '';
            }

            const retention =
                payload.retention_days || {};

            coreEmailRetentionFields.forEach(
                field => {
                    const folder =
                        field.dataset
                            .retentionFolder;

                    field.value =
                        String(
                            retention[folder]
                            ?? 0
                        );
                }
            );

            const visibility =
                payload.source_visibility || {};

            coreEmailSourceFields.forEach(
                field => {
                    const source =
                        field.dataset.emailSource;

                    field.checked =
                        Object.prototype
                            .hasOwnProperty.call(
                                visibility,
                                source
                            )
                            ? Boolean(
                                visibility[source]
                            )
                            : true;
                }
            );
        };

    const saveCoreEmailSettings =
        async () => {
            if (
                !mailboxId
                || !coreEmailSettingsSaveUrl
            ) {
                return;
            }

            const retention = {};

            coreEmailRetentionFields.forEach(
                field => {
                    retention[
                        field.dataset.retentionFolder
                    ] = Number(field.value || 0);
                }
            );

            const visibility = {};

            coreEmailSourceFields.forEach(
                field => {
                    visibility[
                        field.dataset.emailSource
                    ] = field.checked;
                }
            );

            coreEmailSaveSettings.disabled = true;

            try {
                const response =
                    await fetch(
                        coreEmailSettingsSaveUrl,
                        {
                            method: 'POST',

                            headers: {
                                'X-Requested-With':
                                    'XMLHttpRequest',
                                'Accept':
                                    'application/json',
                                'Content-Type':
                                    'application/json',
                                'X-CSRF-TOKEN':
                                    csrfToken
                            },

                            body: JSON.stringify({
                                mailbox_id:
                                    Number(mailboxId),

                                retention_days:
                                    retention,

                                source_visibility:
                                    visibility,

                                forward_address:
                                    coreEmailForwardAddress
                                        ? coreEmailForwardAddress.value.trim()
                                        : ''
                            })
                        }
                    );

                const payload =
                    await response.json();

                if (!response.ok || !payload.ok) {
                    throw new Error(
                        payload.message
                        || 'Unable to save mailbox settings.'
                    );
                }

                coreEmailSaveSettings.textContent =
                    'Saved';

                window.setTimeout(
                    () => {
                        coreEmailSaveSettings.textContent =
                            'Save Settings';
                    },
                    1400
                );
            } catch (error) {
                window.alert(
                    error?.message
                    || 'Unable to save mailbox settings.'
                );
            } finally {
                coreEmailSaveSettings.disabled =
                    false;
            }
        };

    if (coreEmailSaveSettings) {
        coreEmailSaveSettings.addEventListener(
            'click',
            saveCoreEmailSettings
        );
    }

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

                root.dataset.activeDraftId =
                    activeDraftId
                        ? String(activeDraftId)
                        : '';

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
    /*
     * messageViewPanel is declared once by the
     * authoritative Level-2 runtime above.
     */
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

        const token =
            '/messages/0';

        if (!template.includes(token)) {
            messageViewLoading?.classList.add(
                'hidden'
            );

            messageViewContent?.classList.remove(
                'hidden'
            );

            return;
        }

        const url = new URL(
            template.replace(
                token,
                `/messages/${messageId}`
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
                const failureBody =
                    await response.text();

                console.error(
                    '[Core Email] message endpoint failed',
                    {
                        status: response.status,
                        statusText: response.statusText,
                        url: response.url,
                        body: failureBody,
                    }
                );

                throw new Error(
                    'HTTP '
                    + response.status
                    + ' '
                    + response.statusText
                    + (
                        failureBody
                            ? ' — '
                                + failureBody
                                    .replace(/<[^>]*>/g, ' ')
                                    .replace(/\\s+/g, ' ')
                                    .trim()
                                    .slice(0, 300)
                            : ''
                    )
                );
            }

            const payload =
                await response.json();

            const message =
                payload.message || {};

            /* ESUBIZ_CORE_EMAIL_DETAIL_STATE_V1 */
            window.__coreEmailOpenMessage = {
                id: Number(message.id || messageId),
                message,
                replyForward:
                    payload.reply_forward || {},
                attachments:
                    Array.isArray(payload.attachments)
                        ? payload.attachments
                        : [],
            };

            const detailActions =
                document.getElementById(
                    'core-email-message-actions'
                );

            const detailIds = [
                'core-email-view-reply',
                'core-email-view-forward',
                'core-email-view-read',
                'core-email-view-unread',
                'core-email-view-move-spam',
                'core-email-view-move-inbox',
                'core-email-view-edit',
                'core-email-view-send-draft',
                'core-email-view-restore',
                'core-email-view-delete',
                'core-email-view-delete-permanent',
            ];

            detailIds.forEach(id => {
                document.getElementById(id)
                    ?.classList.add('hidden');
            });

            const showDetail = id => {
                document.getElementById(id)
                    ?.classList.remove('hidden');
            };

            const detailFolder =
                String(
                    message.folder || folder || ''
                ).toLowerCase();

            detailActions?.classList.remove('hidden');
            detailActions?.classList.add('flex');

            if (detailFolder === 'inbox') {
                showDetail('core-email-view-reply');
                showDetail('core-email-view-forward');
                showDetail('core-email-view-unread');
                showDetail('core-email-view-move-spam');
                showDetail('core-email-view-delete');
            } else if (detailFolder === 'spam') {
                showDetail('core-email-view-reply');
                showDetail('core-email-view-forward');
                showDetail('core-email-view-unread');
                showDetail('core-email-view-move-inbox');
                showDetail('core-email-view-delete');
            } else if (detailFolder === 'sent') {
                showDetail('core-email-view-forward');
                showDetail('core-email-view-delete');
            } else if (detailFolder === 'draft') {
                showDetail('core-email-view-edit');
                showDetail('core-email-view-send-draft');
                showDetail('core-email-view-delete');
            } else if (detailFolder === 'trash') {
                showDetail('core-email-view-restore');
                showDetail(
                    'core-email-view-delete-permanent'
                );
            }

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
                const attachmentExtension =
                    name => {
                        const value =
                            String(name || '');

                        const dot =
                            value.lastIndexOf('.');

                        if (
                            dot <= 0
                            || dot === value.length - 1
                        ) {
                            return '';
                        }

                        return value
                            .slice(dot + 1)
                            .toLowerCase();
                    };

                const attachmentFileType =
                    item => {
                        const ext =
                            attachmentExtension(
                                item.name
                            );

                        const known = {
                            pdf: 'PDF',
                            doc: 'DOC',
                            docx: 'DOCX',
                            xls: 'XLS',
                            xlsx: 'XLSX',
                            csv: 'CSV',
                            ppt: 'PPT',
                            pptx: 'PPTX',
                            txt: 'TXT',
                            rtf: 'RTF',
                            zip: 'ZIP',
                            rar: 'RAR',
                            '7z': '7Z',
                            jpg: 'JPG',
                            jpeg: 'JPEG',
                            png: 'PNG',
                            gif: 'GIF',
                            webp: 'WEBP',
                            svg: 'SVG',
                            mp3: 'MP3',
                            wav: 'WAV',
                            mp4: 'MP4',
                            mov: 'MOV'
                        };

                        if (
                            ext
                            && known[ext]
                        ) {
                            return known[ext];
                        }

                        if (ext) {
                            return ext
                                .slice(0, 5)
                                .toUpperCase();
                        }

                        const mime =
                            String(
                                item.mime_type || ''
                            );

                        if (
                            mime.startsWith(
                                'image/'
                            )
                        ) {
                            return 'IMG';
                        }

                        if (
                            mime.startsWith(
                                'video/'
                            )
                        ) {
                            return 'VIDEO';
                        }

                        if (
                            mime.startsWith(
                                'audio/'
                            )
                        ) {
                            return 'AUDIO';
                        }

                        return 'FILE';
                    };

                const attachmentReadableSize =
                    bytes => {
                        const size =
                            Number(bytes || 0);

                        if (size < 1024) {
                            return `${size} B`;
                        }

                        if (
                            size
                            < 1024 * 1024
                        ) {
                            return `${
                                (
                                    size / 1024
                                ).toFixed(1)
                            } KB`;
                        }

                        return `${
                            (
                                size
                                / 1024
                                / 1024
                            ).toFixed(1)
                        } MB`;
                    };

                const attachmentCanPreview =
                    item => {
                        const mime =
                            String(
                                item.mime_type || ''
                            )
                                .toLowerCase();

                        return (
                            mime ===
                                'application/pdf'
                            || mime ===
                                'text/plain'
                            || mime ===
                                'image/jpeg'
                            || mime ===
                                'image/png'
                            || mime ===
                                'image/gif'
                            || mime ===
                                'image/webp'
                            || mime.startsWith(
                                'audio/'
                            )
                            || mime.startsWith(
                                'video/'
                            )
                        );
                    };

                attachmentList.innerHTML =
                    attachments.map(item => {
                        const downloadTemplate =
                            root.dataset
                                .attachmentUrlTemplate
                            || '';

                        const previewTemplate =
                            root.dataset
                                .attachmentPreviewUrlTemplate
                            || '';

                        const downloadToken =
                            '/attachments/0/download';

                        const previewToken =
                            '/attachments/0/preview';

                        let downloadUrl = '#';
                        let previewUrl = '#';

                        if (
                            Number(item.id) > 0
                            && downloadTemplate
                                .includes(
                                    downloadToken
                                )
                        ) {
                            const url =
                                new URL(
                                    downloadTemplate
                                        .replace(
                                            downloadToken,
                                            `/attachments/${Number(item.id)}/download`
                                        ),
                                    window.location.origin
                                );

                            url.searchParams.set(
                                'mailbox',
                                String(mailboxId)
                            );

                            downloadUrl =
                                url.toString();
                        }

                        if (
                            Number(item.id) > 0
                            && previewTemplate
                                .includes(
                                    previewToken
                                )
                        ) {
                            const url =
                                new URL(
                                    previewTemplate
                                        .replace(
                                            previewToken,
                                            `/attachments/${Number(item.id)}/preview`
                                        ),
                                    window.location.origin
                                );

                            url.searchParams.set(
                                'mailbox',
                                String(mailboxId)
                            );

                            previewUrl =
                                url.toString();
                        }

                        const type =
                            attachmentFileType(
                                item
                            );

                        const size =
                            attachmentReadableSize(
                                item.size
                            );

                        const previewButton =
                            attachmentCanPreview(
                                item
                            )
                                ? `
                                    <button
                                        type="button"
                                        data-core-email-attachment-preview
                                        data-preview-url="${escapeSearchHtml(previewUrl)}"
                                        data-preview-name="${escapeSearchHtml(item.name || 'Attachment')}"
                                        data-preview-type="${escapeSearchHtml(type)}"
                                        data-preview-size="${escapeSearchHtml(size)}"
                                        class="font-semibold text-blue-700 hover:underline"
                                    >
                                        Preview
                                    </button>

                                    <span
                                        class="text-slate-300"
                                    >
                                        |
                                    </span>
                                `
                                : '';

                        return `
                            <div
                                class="flex max-w-full items-center gap-3 rounded-xl border border-slate-200 bg-slate-50 p-3"
                            >
                                <div
                                    class="flex h-10 w-10 shrink-0 items-center justify-center rounded-md border border-slate-200 bg-white text-[10px] font-bold text-slate-600"
                                >
                                    ${escapeSearchHtml(type)}
                                </div>

                                <div
                                    class="min-w-0 flex-1"
                                >
                                    <div
                                        class="truncate text-sm font-semibold text-slate-700"
                                        title="${escapeSearchHtml(item.name || 'Attachment')}"
                                    >
                                        ${escapeSearchHtml(
                                            item.name
                                            || 'Attachment'
                                        )}
                                    </div>

                                    <div
                                        class="mt-0.5 text-[10px] text-slate-500"
                                    >
                                        ${escapeSearchHtml(type)}
                                        •
                                        ${escapeSearchHtml(size)}
                                    </div>

                                    <div
                                        class="mt-2 flex flex-wrap items-center gap-2 text-xs"
                                    >
                                        ${previewButton}

                                        <a
                                            href="${escapeSearchHtml(downloadUrl)}"
                                            class="font-semibold text-blue-700 hover:underline"
                                        >
                                            Download
                                        </a>
                                    </div>
                                </div>
                            </div>
                        `;
                    }).join('');

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
            const detail =
                error instanceof Error
                    ? error.message
                    : String(error);

            console.error(
                '[Core Email] openMessage failed',
                {
                    messageId,
                    mailboxId,
                    template:
                        root.dataset.messageUrlTemplate,
                    error,
                }
            );

            if (messageViewLoading) {
                messageViewLoading.textContent =
                    'This email could not be opened: '
                    + detail;
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

    /*
     * ESUBIZ_CORE_EMAIL_ATTACHMENT_PREVIEW_RUNTIME_V1
     */
    const attachmentPreviewModal =
        document.getElementById(
            'core-email-attachment-preview-modal'
        );

    const attachmentPreviewFrame =
        document.getElementById(
            'core-email-attachment-preview-frame'
        );

    const attachmentPreviewTitle =
        document.getElementById(
            'core-email-attachment-preview-title'
        );

    const attachmentPreviewMeta =
        document.getElementById(
            'core-email-attachment-preview-meta'
        );

    const closeAttachmentPreview =
        () => {
            if (
                !attachmentPreviewModal
            ) {
                return;
            }

            attachmentPreviewModal
                .classList.add('hidden');

            attachmentPreviewModal
                .classList.remove('flex');

            if (
                attachmentPreviewFrame
            ) {
                attachmentPreviewFrame
                    .removeAttribute('src');
            }

            document.body
                .classList.remove(
                    'overflow-hidden'
                );
        };

    document.addEventListener(
        'click',
        event => {
            const previewButton =
                event.target.closest(
                    '[data-core-email-attachment-preview]'
                );

            if (previewButton) {
                event.preventDefault();

                const url =
                    previewButton.dataset
                        .previewUrl
                    || '';

                if (
                    !url
                    || url === '#'
                ) {
                    return;
                }

                if (
                    attachmentPreviewTitle
                ) {
                    attachmentPreviewTitle
                        .textContent =
                            previewButton.dataset
                                .previewName
                            || 'Attachment Preview';
                }

                if (
                    attachmentPreviewMeta
                ) {
                    attachmentPreviewMeta
                        .textContent = [
                            previewButton.dataset
                                .previewType
                                || 'FILE',
                            previewButton.dataset
                                .previewSize
                                || ''
                        ]
                            .filter(Boolean)
                            .join(' • ');
                }

                if (
                    attachmentPreviewFrame
                ) {
                    attachmentPreviewFrame.src =
                        url;
                }

                attachmentPreviewModal
                    ?.classList.remove(
                        'hidden'
                    );

                attachmentPreviewModal
                    ?.classList.add(
                        'flex'
                    );

                document.body
                    .classList.add(
                        'overflow-hidden'
                    );

                return;
            }

            if (
                event.target.closest(
                    '#core-email-attachment-preview-close'
                )
            ) {
                closeAttachmentPreview();
                return;
            }

            if (
                event.target ===
                    attachmentPreviewModal
            ) {
                closeAttachmentPreview();
            }
        }
    );

    document.addEventListener(
        'keydown',
        event => {
            if (
                event.key === 'Escape'
                && attachmentPreviewModal
                && !attachmentPreviewModal
                    .classList.contains(
                        'hidden'
                    )
            ) {
                closeAttachmentPreview();
            }
        }
    );

    /*
     * ESUBIZ_CORE_EMAIL_DETAIL_ACTION_RUNTIME_V1
     */
    document.addEventListener(
        'click',
        async event => {
            const current =
                window.__coreEmailOpenMessage;

            const actionButton =
                event.target.closest(
                    '[data-detail-action]'
                );

            if (actionButton) {
                event.preventDefault();

                if (!current?.id) {
                    return;
                }

                const action =
                    actionButton.dataset
                        .detailAction;

                if (
                    (
                        action === 'delete'
                        || action
                            === 'delete_permanently'
                    )
                    && !window.confirm(
                        action
                            === 'delete_permanently'
                            ? 'Permanently delete this email? This cannot be undone.'
                            : 'Delete this email?'
                    )
                ) {
                    return;
                }

                actionButton.disabled = true;

                try {
                    await performV17MessageAction(
                        current.id,
                        action
                    );

                    messageViewPanel
                        ?.classList.add('hidden');

                    messageListPanel
                        ?.classList.remove('hidden');

                    window.__coreEmailOpenMessage =
                        null;

                    await loadMessages();
                } catch (error) {
                    console.error(
                        '[Core Email] detail action failed',
                        error
                    );

                    window.alert(
                        error?.message
                        || 'Unable to complete this email action.'
                    );
                } finally {
                    actionButton.disabled = false;
                }

                return;
            }

            if (
                event.target.closest(
                    '#core-email-view-reply'
                )
            ) {
                event.preventDefault();

                if (!current) {
                    return;
                }

                const reply =
                    current.replyForward?.reply
                    || {};

                openDraftInCompose({
                    id: null,
                    folder: 'draft',
                    to: reply.to || '',
                    cc: '',
                    bcc: '',
                    subject:
                        reply.subject || '',
                    body: '',
                    body_html: '',
                    attachments: [],
                });

                return;
            }

            if (
                event.target.closest(
                    '#core-email-view-forward'
                )
            ) {
                event.preventDefault();

                if (!current) {
                    return;
                }

                const forward =
                    current.replyForward?.forward
                    || {};

                const forwardedBody = [
                    '',
                    '',
                    '---------- Forwarded message ----------',
                    `From: ${
                        forward.original_from || ''
                    }`,
                    `To: ${
                        forward.original_to || ''
                    }`,
                    `Subject: ${
                        forward.original_subject || ''
                    }`,
                    '',
                    forward.body || '',
                ].join('\n');

                openDraftInCompose({
                    id: null,
                    folder: 'draft',
                    to: '',
                    cc: '',
                    bcc: '',
                    subject:
                        forward.subject || 'Fwd:',
                    body: forwardedBody,
                    body_html: '',
                    attachments: [],
                });

                return;
            }

            if (
                event.target.closest(
                    '#core-email-view-edit'
                )
                || event.target.closest(
                    '#core-email-view-send-draft'
                )
            ) {
                event.preventDefault();

                if (!current?.message) {
                    return;
                }

                openDraftInCompose({
                    ...current.message,
                    attachments:
                        current.attachments || [],
                });
            }
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


{{-- ESUBIZ_CORE_EMAIL_LEVEL1_NAVIGATION_V8B --}}
<script>
document.addEventListener('DOMContentLoaded', () => {
    const senderTab =
        document.getElementById(
            'core-email-sender-level1'
        );

    const senderWorkspace =
        document.getElementById(
            'core-email-sender-workspace'
        );

    const addTab =
        document.getElementById(
            'core-email-add-address'
        );

    const addWorkspace =
        document.getElementById(
            'core-email-add-address-workspace'
        );

    const mailboxWorkspace =
        document.getElementById(
            'core-email-mailbox-workspace'
        );

    const mailboxTabs =
        Array.from(
            document.querySelectorAll(
                '[data-email-level1="mailbox"]'
            )
        );

    if (
        !senderTab
        || !senderWorkspace
        || !addTab
        || !addWorkspace
        || !mailboxWorkspace
    ) {
        return;
    }

    const level1Tabs = [
        senderTab,
        ...mailboxTabs,
        addTab
    ];

    const paint = active => {
        level1Tabs.forEach(tab => {
            tab.classList.remove(
                'bg-blue-600',
                'bg-blue-700',
                'bg-blue-800',
                'bg-slate-900',
                'bg-white',
                'text-slate-600'
            );

            tab.classList.add(
                tab === active
                    ? 'bg-blue-800'
                    : 'bg-blue-600',
                'text-white'
            );
        });
    };

    const hideAll = () => {
        senderWorkspace.classList.add(
            'hidden'
        );

        addWorkspace.classList.add(
            'hidden'
        );

        mailboxWorkspace.classList.add(
            'hidden'
        );
    };

    senderTab.addEventListener(
        'click',
        () => {
            hideAll();

            senderWorkspace.classList.remove(
                'hidden'
            );

            paint(senderTab);
        }
    );

    addTab.addEventListener(
        'click',
        () => {
            hideAll();

            addWorkspace.classList.remove(
                'hidden'
            );

            paint(addTab);
        }
    );

    mailboxTabs.forEach(tab => {
        /*
         * This listener controls Level-1 visibility only.
         * The existing Core Email listener separately calls
         * activateMailbox(tab).
         */
        tab.addEventListener(
            'click',
            () => {
                hideAll();

                mailboxWorkspace.classList.remove(
                    'hidden'
                );

                paint(tab);
            }
        );
    });

    /*
     * DEFAULT:
     * First real email address opens first.
     * Email Sender remains visually first in Level 1.
     */
    hideAll();

    if (mailboxTabs.length > 0) {
        mailboxWorkspace.classList.remove(
            'hidden'
        );

        paint(mailboxTabs[0]);
    } else {
        addWorkspace.classList.remove(
            'hidden'
        );

        paint(addTab);
    }
});
</script>



{{-- ESUBIZ_CORE_EMAIL_ATTACHMENT_PREVIEW_JS_V2 --}}
<script>
document.addEventListener('DOMContentLoaded', () => {
    const input =
        document.getElementById(
            'core-email-compose-attachments'
        );

    const wrapper =
        document.getElementById(
            'core-email-compose-attachment-previews'
        );

    const list =
        document.getElementById(
            'core-email-compose-attachment-preview-list'
        );

    if (!input || !wrapper || !list) {
        return;
    }

    /*
     * ESUBIZ_CORE_EMAIL_ATTACHMENT_SELECTION_V2
     *
     * This is the canonical pending-upload selection for
     * Create Email. Multiple selection events accumulate.
     */
    let selectedFiles = [];

    const extensionOf = name => {
        const value =
            String(name || '');

        const dot =
            value.lastIndexOf('.');

        if (
            dot <= 0
            || dot === value.length - 1
        ) {
            return '';
        }

        return value
            .slice(dot + 1)
            .toLowerCase();
    };

    const fileType = file => {
        const ext =
            extensionOf(file.name);

        const known = {
            pdf: 'PDF',
            doc: 'DOC',
            docx: 'DOCX',
            xls: 'XLS',
            xlsx: 'XLSX',
            csv: 'CSV',
            ppt: 'PPT',
            pptx: 'PPTX',
            txt: 'TXT',
            rtf: 'RTF',
            zip: 'ZIP',
            rar: 'RAR',
            '7z': '7Z',
            jpg: 'JPG',
            jpeg: 'JPEG',
            png: 'PNG',
            gif: 'GIF',
            webp: 'WEBP',
            svg: 'SVG',
            mp3: 'MP3',
            wav: 'WAV',
            mp4: 'MP4',
            mov: 'MOV'
        };

        if (ext && known[ext]) {
            return known[ext];
        }

        if (ext) {
            return ext
                .slice(0, 5)
                .toUpperCase();
        }

        const mime =
            String(file.type || '');

        if (mime.startsWith('image/')) {
            return 'IMG';
        }

        if (mime.startsWith('video/')) {
            return 'VIDEO';
        }

        if (mime.startsWith('audio/')) {
            return 'AUDIO';
        }

        return 'FILE';
    };

    const readableSize = bytes => {
        const size =
            Number(bytes || 0);

        if (size < 1024) {
            return `${size} B`;
        }

        if (size < 1024 * 1024) {
            return `${
                (size / 1024).toFixed(1)
            } KB`;
        }

        return `${
            (
                size
                / 1024
                / 1024
            ).toFixed(1)
        } MB`;
    };

    const keyOf = file => [
        file.name,
        file.size,
        file.lastModified
    ].join('::');

    const syncNativeInput = () => {
        const transfer =
            new DataTransfer();

        selectedFiles.forEach(file => {
            transfer.items.add(file);
        });

        input.files =
            transfer.files;
    };

    const clearPreview = () => {
        list.replaceChildren();

        if (selectedFiles.length === 0) {
            wrapper.classList.add(
                'hidden'
            );
        } else {
            wrapper.classList.remove(
                'hidden'
            );
        }
    };

    const render = () => {
        clearPreview();

        selectedFiles.forEach(
            (file, index) => {
                const card =
                    document.createElement(
                        'div'
                    );

                card.className =
                    'flex max-w-full items-center gap-2 rounded-lg border border-slate-200 bg-slate-50 px-2 py-2 shadow-sm';

                const icon =
                    document.createElement(
                        'div'
                    );

                icon.className =
                    'flex h-10 w-10 shrink-0 items-center justify-center overflow-hidden rounded-md border border-slate-200 bg-white text-[10px] font-bold text-slate-600';

                if (
                    String(file.type || '')
                        .startsWith('image/')
                ) {
                    const image =
                        document.createElement(
                            'img'
                        );

                    const url =
                        URL.createObjectURL(
                            file
                        );

                    image.src =
                        url;

                    image.alt =
                        file.name;

                    image.className =
                        'h-full w-full object-cover';

                    image.addEventListener(
                        'load',
                        () => {
                            URL.revokeObjectURL(
                                url
                            );
                        },
                        {
                            once: true
                        }
                    );

                    icon.appendChild(
                        image
                    );
                } else {
                    icon.textContent =
                        fileType(file);
                }

                const info =
                    document.createElement(
                        'div'
                    );

                info.className =
                    'min-w-0 max-w-52';

                const name =
                    document.createElement(
                        'div'
                    );

                name.className =
                    'truncate text-xs font-semibold text-slate-700';

                name.textContent =
                    file.name;

                name.title =
                    file.name;

                const meta =
                    document.createElement(
                        'div'
                    );

                meta.className =
                    'mt-0.5 text-[10px] text-slate-500';

                meta.textContent =
                    `${fileType(file)} • ${readableSize(file.size)}`;

                info.append(
                    name,
                    meta
                );

                const remove =
                    document.createElement(
                        'button'
                    );

                remove.type =
                    'button';

                remove.className =
                    'ml-1 flex h-7 w-7 shrink-0 items-center justify-center rounded-full text-lg leading-none text-slate-400 hover:bg-red-50 hover:text-red-600';

                remove.textContent =
                    '×';

                remove.title =
                    `Remove ${file.name}`;

                remove.setAttribute(
                    'aria-label',
                    `Remove ${file.name}`
                );

                remove.addEventListener(
                    'click',
                    () => {
                        selectedFiles.splice(
                            index,
                            1
                        );

                        syncNativeInput();
                        render();
                    }
                );

                card.append(
                    icon,
                    info,
                    remove
                );

                list.appendChild(
                    card
                );
            }
        );
    };

    input.addEventListener(
        'change',
        () => {
            const incoming =
                Array.from(
                    input.files || []
                );

            const existing =
                new Set(
                    selectedFiles.map(
                        keyOf
                    )
                );

            incoming.forEach(file => {
                const key =
                    keyOf(file);

                if (existing.has(key)) {
                    return;
                }

                selectedFiles.push(
                    file
                );

                existing.add(key);
            });

            syncNativeInput();
            render();
        }
    );

    /*
     * Save/Send can dispatch this after the canonical
     * attachment operation succeeds.
     */
    document.addEventListener(
        'esubiz:core-email-attachments-reset',
        () => {
            selectedFiles = [];

            syncNativeInput();
            render();
        }
    );
});
</script>


{{-- ESUBIZ_CORE_EMAIL_SAVE_DRAFT_RUNTIME_V10 --}}
<script>
document.addEventListener('DOMContentLoaded', () => {
    const root =
        document.getElementById('core-email-workspace');

    const button =
        document.getElementById('core-email-save-draft');

    if (!root || !button) {
        return;
    }

    const field = id =>
        document.getElementById(id);

    const to =
        field('core-email-compose-to');

    const cc =
        field('core-email-compose-cc');

    const bcc =
        field('core-email-compose-bcc');

    const subject =
        field('core-email-compose-subject');

    const body =
        field('core-email-compose-body');

    const attachments =
        field('core-email-compose-attachments');

    const status =
        field('core-email-compose-status');

    const csrf =
        document.querySelector(
            'meta[name="csrf-token"]'
        )?.content || '';

    let draftId = null;
    let saving = false;

    const mailboxId = () => {
        const tabs =
            Array.from(
                root.querySelectorAll(
                    '[data-email-level1="mailbox"]'
                )
            );

        const active =
            tabs.find(tab =>
                tab.classList.contains('bg-blue-800')
            );

        return Number(
            (
                active
                || tabs[0]
            )?.dataset.mailboxId || 0
        );
    };

    const notify = (
        message,
        success = false
    ) => {
        if (!status) {
            return;
        }

        status.textContent = message;

        status.className =
            success
                ? 'rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700'
                : 'rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700';
    };

    button.addEventListener(
        'click',
        async event => {
            event.preventDefault();
            event.stopImmediatePropagation();

            if (saving) {
                return;
            }

            const mailbox =
                mailboxId();

            if (!mailbox) {
                notify(
                    'Select an email address first.'
                );
                return;
            }

            const url =
                root.dataset.saveDraftUrl;

            if (!url) {
                notify(
                    'Draft endpoint is unavailable.'
                );
                return;
            }

            saving = true;
            button.disabled = true;

            const original =
                button.textContent;

            button.textContent =
                'Saving...';

            try {
                const data =
                    new FormData();

                data.append(
                    'mailbox',
                    String(mailbox)
                );

                data.append(
                    'to',
                    to?.value || ''
                );

                data.append(
                    'cc',
                    cc?.value || ''
                );

                data.append(
                    'bcc',
                    bcc?.value || ''
                );

                data.append(
                    'subject',
                    subject?.value || ''
                );

                data.append(
                    'body',
                    body?.innerText || ''
                );

                data.append(
                    'body_html',
                    body?.innerHTML || ''
                );

                if (draftId) {
                    data.append(
                        'draft_id',
                        String(draftId)
                    );
                }

                Array.from(
                    attachments?.files || []
                ).forEach(file => {
                    data.append(
                        'attachments[]',
                        file
                    );
                });

                const response =
                    await fetch(
                        url,
                        {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': csrf,
                                'Accept': 'application/json'
                            },
                            credentials: 'same-origin',
                            body: data
                        }
                    );

                let payload = {};

                try {
                    payload =
                        await response.json();
                } catch (_) {
                    payload = {};
                }

                if (!response.ok) {
                    const errors =
                        payload?.errors
                            ? Object.values(
                                payload.errors
                            )
                                .flat()
                                .join(' ')
                            : '';

                    throw new Error(
                        errors
                        || payload?.message
                        || `Draft could not be saved (${response.status}).`
                    );
                }

                draftId =
                    Number(
                        payload?.draft_id
                        || payload?.message?.id
                        || draftId
                        || 0
                    ) || null;

                if (
                    attachments
                    && attachments.files.length
                ) {
                    document.dispatchEvent(
                        new CustomEvent(
                            'esubiz:core-email-attachments-reset'
                        )
                    );
                }

                notify(
                    'Draft saved successfully.',
                    true
                );
            } catch (error) {
                notify(
                    error?.message
                    || 'Draft could not be saved.'
                );
            } finally {
                saving = false;
                button.disabled = false;
                button.textContent =
                    original;
            }
        },
        true
    );
});
</script>




{{-- ESUBIZ_CORE_EMAIL_REAL_SEND_JS_V1 --}}
<script>
document.addEventListener('DOMContentLoaded', () => {
    const root =
        document.getElementById(
            'core-email-workspace'
        );

    const sendButton =
        document.getElementById(
            'core-email-send'
        );

    if (!root || !sendButton) {
        return;
    }

    const get =
        id => document.getElementById(id);

    const to =
        get('core-email-compose-to');

    const cc =
        get('core-email-compose-cc');

    const bcc =
        get('core-email-compose-bcc');

    const subject =
        get('core-email-compose-subject');

    const body =
        get('core-email-compose-body');

    const attachmentInput =
        get('core-email-compose-attachments');

    const status =
        get('core-email-compose-status');

    const csrf =
        document.querySelector(
            'meta[name="csrf-token"]'
        )?.getAttribute('content') || '';

    let sending = false;

    const selectedMailboxId = () => {
        const tabs =
            Array.from(
                root.querySelectorAll(
                    '[data-email-level1="mailbox"]'
                )
            );

        const selected =
            tabs.find(
                tab =>
                    tab.classList.contains(
                        'bg-blue-800'
                    )
            );

        return Number(
            selected?.dataset.mailboxId
                || 0
        );
    };

    const showStatus = (
        message,
        success = false
    ) => {
        if (!status) {
            return;
        }

        status.textContent =
            String(message || '');

        status.className =
            success
                ? 'rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700'
                : 'rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700';
    };

    const value =
        element =>
            element
                ? String(element.value || '')
                : '';

    const bodyText = () => {
        if (!body) {
            return '';
        }

        if (
            body instanceof HTMLTextAreaElement
            || body instanceof HTMLInputElement
        ) {
            return String(
                body.value || ''
            );
        }

        return String(
            body.innerText || ''
        );
    };

    const bodyHtml = () => {
        if (!body) {
            return '';
        }

        if (
            body instanceof HTMLTextAreaElement
            || body instanceof HTMLInputElement
        ) {
            return String(
                body.value || ''
            );
        }

        return String(
            body.innerHTML || ''
        );
    };

    sendButton.addEventListener(
        'click',
        async event => {
            /*
             * This is the single authoritative Send owner.
             * Capture phase prevents any obsolete/placeholder Send
             * handler from causing a second request.
             */
            event.preventDefault();
            event.stopImmediatePropagation();

            if (sending) {
                return;
            }

            const mailboxId =
                selectedMailboxId();

            if (!mailboxId) {
                showStatus(
                    'Select an email address before sending.'
                );
                return;
            }

            if (
                !to
                || value(to).trim() === ''
            ) {
                showStatus(
                    'Enter at least one recipient.'
                );

                to?.focus();

                return;
            }

            const sendUrl =
                root.dataset.sendUrl || '';

            if (!sendUrl) {
                showStatus(
                    'The email Send endpoint is unavailable.'
                );
                return;
            }

            const formData =
                new FormData();

            formData.append(
                'mailbox',
                String(mailboxId)
            );

            formData.append(
                'to',
                value(to)
            );

            formData.append(
                'cc',
                value(cc)
            );

            formData.append(
                'bcc',
                value(bcc)
            );

            formData.append(
                'subject',
                value(subject)
            );

            formData.append(
                'body',
                bodyText()
            );

            formData.append(
                'body_html',
                bodyHtml()
            );

            const draftId =
                Number(
                    root.dataset.activeDraftId
                        || 0
                );

            if (draftId > 0) {
                formData.append(
                    'draft_id',
                    String(draftId)
                );
            }

            Array.from(
                attachmentInput?.files || []
            ).forEach(file => {
                formData.append(
                    'attachments[]',
                    file
                );
            });

            sending = true;
            sendButton.disabled = true;

            const originalText =
                sendButton.textContent;

            sendButton.textContent =
                'Sending...';

            try {
                const response =
                    await fetch(
                        sendUrl,
                        {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN':
                                    csrf,

                                'Accept':
                                    'application/json',

                                'X-Requested-With':
                                    'XMLHttpRequest',
                            },
                            credentials:
                                'same-origin',
                            body:
                                formData,
                        }
                    );

                const payload =
                    await response
                        .json()
                        .catch(
                            () => ({})
                        );

                if (!response.ok) {
                    if (payload?.draft_id) {
                        root.dataset.activeDraftId =
                            String(
                                payload.draft_id
                            );
                    }

                    const validationErrors =
                        payload?.errors
                            ? Object.values(
                                payload.errors
                            )
                                .flat()
                                .join(' ')
                            : '';

                    throw new Error(
                        validationErrors
                        || payload?.message
                        || `Email could not be sent (${response.status}).`
                    );
                }

                root.dataset.activeDraftId =
                    '';

                if (attachmentInput) {
                    attachmentInput.value =
                        '';
                }

                showStatus(
                    payload?.message
                        || 'Email sent successfully.',
                    true
                );

                /*
                 * Notify the authoritative folder runtime. If V17
                 * does not currently consume this event, the Sent
                 * folder will still show the message when selected.
                 */
                document.dispatchEvent(
                    new CustomEvent(
                        'esubiz:core-email-sent',
                        {
                            detail: {
                                messageId:
                                    Number(
                                        payload?.message_id
                                            || 0
                                    )
                            }
                        }
                    )
                );
            } catch (error) {
                showStatus(
                    error?.message
                        || 'Email could not be sent.'
                );
            } finally {
                sending = false;
                sendButton.disabled =
                    false;
                sendButton.textContent =
                    originalText;
            }
        },
        true
    );
});
</script>








@endsection
