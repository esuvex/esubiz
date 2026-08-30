@extends('admin.layouts.app')

@section('title', 'AI Settings')

@section('content')

<div class="mx-auto max-w-5xl space-y-8">

    <div>
        <h1 class="text-2xl font-black text-slate-950">
            AI Settings
        </h1>

        <p class="mt-2 text-sm text-slate-500">
            Global Esubiz AI configuration.
        </p>
    </div>


    @if(session('success'))
        <div
            class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700"
        >
            {{ session('success') }}
        </div>
    @endif


    <div
        class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm"
    >

        <div class="mb-7">

            <h2 class="text-lg font-black text-slate-950">
                Global Chat Appearance
            </h2>

            <p class="mt-1 text-sm text-slate-500">
                These colors apply to AI chat throughout the Esubiz ecosystem.
            </p>

        </div>


        <form
            method="POST"
            action="{{ route('admin.ai.settings.chat.update') }}"
            class="space-y-7"
        >
            @csrf
            @method('PATCH')



            <div
                class="rounded-2xl border border-slate-200 bg-slate-50 p-5"
            >
                <div
                    class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
                >
                    <div class="max-w-2xl">
                        <div class="text-sm font-black text-slate-900">
                            Allow Website Chat Color Customization
                        </div>

                        <p class="mt-1 text-sm text-slate-500">
                            Allow site admins to override the global AI and user
                            message bubble and text colors on their websites.
                            When disabled, Esubiz global chat colors are authoritative.
                        </p>
                    </div>

                    <label
                        class="inline-flex cursor-pointer items-center gap-3"
                    >
                        <input
                            type="checkbox"
                            name="allow_user_chat_color_customization"
                            value="1"
                            @checked(
                                old(
                                    'allow_user_chat_color_customization',
                                    $chat->allow_user_chat_color_customization
                                        ?? true
                                )
                            )
                            class="h-5 w-5 rounded border-slate-300 text-blue-600 focus:ring-blue-500"
                        >

                        <span class="text-sm font-bold text-slate-700">
                            Allow
                        </span>
                    </label>
                </div>
            </div>


            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">

                <div>
                    <label class="mb-2 block text-sm font-bold text-slate-700">
                        AI Chat Bubble
                    </label>

                    <input
                        type="color"
                        name="ai_bubble_color"
                        value="{{ old('ai_bubble_color', $chat->ai_bubble_color ?? '#0b1f3a') }}"
                        class="h-14 w-24 cursor-pointer rounded-xl border border-slate-300 bg-white p-1"
                    >
                </div>


                <div>
                    <label class="mb-2 block text-sm font-bold text-slate-700">
                        AI Text
                    </label>

                    <input
                        type="color"
                        name="ai_text_color"
                        value="{{ old('ai_text_color', $chat->ai_text_color ?? '#ffffff') }}"
                        class="h-14 w-24 cursor-pointer rounded-xl border border-slate-300 bg-white p-1"
                    >
                </div>


                <div>
                    <label class="mb-2 block text-sm font-bold text-slate-700">
                        User Chat Bubble
                    </label>

                    <input
                        type="color"
                        name="user_bubble_color"
                        value="{{ old('user_bubble_color', $chat->user_bubble_color ?? '#f1f5f9') }}"
                        class="h-14 w-24 cursor-pointer rounded-xl border border-slate-300 bg-white p-1"
                    >
                </div>


                <div>
                    <label class="mb-2 block text-sm font-bold text-slate-700">
                        User Text
                    </label>

                    <input
                        type="color"
                        name="user_text_color"
                        value="{{ old('user_text_color', $chat->user_text_color ?? '#0f172a') }}"
                        class="h-14 w-24 cursor-pointer rounded-xl border border-slate-300 bg-white p-1"
                    >
                </div>

            </div>


            {{-- ESUBIZ_GLOBAL_AI_CHAT_LIMITS_V1 --}}
            <div class="border-t border-slate-100 pt-7">

                <div class="mb-6">

                    <h3 class="text-base font-black text-slate-950">
                        Chat & Upload Limits
                    </h3>

                    <p class="mt-1 text-sm text-slate-500">
                        Set the global limits used by Esubiz AI chat.
                    </p>

                </div>


                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">

                    <div>

                        <label class="mb-2 block text-sm font-bold text-slate-700">
                            Characters Per Message
                        </label>

                        <input
                            type="number"
                            name="max_message_characters"
                            min="100"
                            max="100000"
                            step="1"
                            required
                            value="{{ old('max_message_characters', $chat->max_message_characters ?? 10000) }}"
                            class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm"
                        >

                        <p class="mt-2 text-xs text-slate-500">
                            Maximum number of text characters allowed in one message.
                        </p>

                    </div>


                    <div>

                        <label class="mb-2 block text-sm font-bold text-slate-700">
                            Messages Per Conversation
                        </label>

                        <input
                            type="number"
                            name="max_conversation_messages"
                            min="1"
                            max="1000"
                            step="1"
                            required
                            value="{{ old('max_conversation_messages', $chat->max_conversation_messages ?? 100) }}"
                            class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm"
                        >

                        <p class="mt-2 text-xs text-slate-500">
                            Maximum number of messages allowed before a new chat is required.
                        </p>

                    </div>


                    <div>

                        <label class="mb-2 block text-sm font-bold text-slate-700">
                            Photos Per Message
                        </label>

                        <input
                            type="number"
                            name="max_photos_per_message"
                            min="0"
                            max="20"
                            step="1"
                            required
                            value="{{ old('max_photos_per_message', $chat->max_photos_per_message ?? 5) }}"
                            class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm"
                        >

                        <p class="mt-2 text-xs text-slate-500">
                            Maximum photos that can be attached to one message. Use 0 to disable photos.
                        </p>

                    </div>


                    <div>

                        <label class="mb-2 block text-sm font-bold text-slate-700">
                            Maximum Photo Size
                        </label>

                        <div class="flex items-center gap-3">

                            <input
                                type="number"
                                name="max_photo_size_mb"
                                min="1"
                                max="50"
                                step="1"
                                required
                                value="{{ old('max_photo_size_mb', $chat->max_photo_size_mb ?? 10) }}"
                                class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm"
                            >

                            <span class="shrink-0 text-sm font-bold text-slate-600">
                                MB
                            </span>

                        </div>

                        <p class="mt-2 text-xs text-slate-500">
                            Maximum size of each uploaded photo. Example: 1 MB or 2 MB.
                        </p>

                    </div>

                </div>

            </div>


            <div class="border-t border-slate-100 pt-6">

                <button
                    type="submit"
                    class="rounded-xl bg-blue-600 px-6 py-3 text-sm font-bold text-white transition hover:bg-blue-700"
                >
                    Save Global Chat Settings
                </button>

            </div>

        </form>

    </div>

</div>

@endsection
