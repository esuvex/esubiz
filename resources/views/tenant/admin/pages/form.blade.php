<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        {{ $page ? 'Edit Page' : 'Add Page' }}
        - {{ $website->name }}
    </title>

    <script src="https://cdn.tailwindcss.com"></script>

</head>


<body class="min-h-screen bg-slate-100 text-slate-900">

<div class="mx-auto max-w-5xl p-5 sm:p-8">

    <div class="mb-6">

        <a
            href="{{ route(
                'tenant.cms.pages.index',
                ['subdomain' => $website->subdomain]
            ) }}"
            class="text-sm font-bold text-blue-600"
        >
            ← Pages
        </a>

    </div>


    <div
        class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8"
    >

        <h1 class="text-3xl font-black">

            {{ $page
                ? 'Edit Page'
                : 'Add Page' }}

        </h1>


        <p class="mt-2 text-slate-500">

            {{ $page
                ? 'Update this website page.'
                : 'Create a new page for your website.' }}

        </p>


        @if($errors->any())

            <div
                class="mt-6 rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-700"
            >

                <ul class="list-disc pl-5">

                    @foreach($errors->all() as $error)

                        <li>{{ $error }}</li>

                    @endforeach

                </ul>

            </div>

        @endif


        <form
            method="POST"
            action="{{ $page
                ? route(
                    'tenant.cms.pages.update',
                    [
                        'subdomain' =>
                            $website->subdomain,
                        'page' =>
                            $page->id,
                    ]
                )
                : route(
                    'tenant.cms.pages.store',
                    [
                        'subdomain' =>
                            $website->subdomain,
                    ]
                ) }}"
            class="mt-7 space-y-6"
        >

            @csrf

            @if($page)
                @method('PUT')
            @endif


            <div>

                <label class="text-sm font-black">
                    Page Title
                </label>

                <input
                    type="text"
                    name="title"
                    required
                    value="{{ old(
                        'title',
                        $page->title ?? ''
                    ) }}"
                    class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3 outline-none focus:border-blue-500"
                >

            </div>


            <div>

                <label class="text-sm font-black">
                    URL Slug
                </label>

                <input
                    type="text"
                    name="slug"
                    value="{{ old(
                        'slug',
                        $page->slug ?? ''
                    ) }}"
                    placeholder="about-us"
                    class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3 outline-none focus:border-blue-500"
                >

                <p
                    class="mt-2 text-xs text-slate-400"
                >
                    Leave empty on a new page to generate it automatically from the title.
                </p>

            </div>


            <div>

                <label class="text-sm font-black">
                    Content
                </label>

                <textarea
                    name="content"
                    rows="14"
                    class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3 outline-none focus:border-blue-500"
                >{{ old(
                    'content',
                    $page->content ?? ''
                ) }}</textarea>

            </div>


            <div
                class="grid gap-5 sm:grid-cols-2"
            >

                <div>

                    <label class="text-sm font-black">
                        Status
                    </label>

                    <select
                        name="status"
                        class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3"
                    >

                        <option
                            value="published"
                            @selected(
                                old(
                                    'status',
                                    $page->status
                                        ?? 'published'
                                ) === 'published'
                            )
                        >
                            Published
                        </option>

                        <option
                            value="draft"
                            @selected(
                                old(
                                    'status',
                                    $page->status
                                        ?? ''
                                ) === 'draft'
                            )
                        >
                            Draft
                        </option>

                    </select>

                </div>


                <div>

                    <label class="text-sm font-black">
                        Homepage
                    </label>

                    <label
                        class="mt-3 flex items-center gap-3 rounded-xl border border-slate-200 p-4"
                    >

                        <input
                            type="checkbox"
                            name="is_homepage"
                            value="1"
                            @checked(
                                old(
                                    'is_homepage',
                                    $page->is_homepage
                                        ?? false
                                )
                            )
                        >

                        <span class="text-sm font-bold">
                            Set as website homepage
                        </span>

                    </label>

                </div>

            </div>


            <div
                class="flex justify-end gap-3 border-t border-slate-100 pt-6"
            >

                <a
                    href="{{ route(
                        'tenant.cms.pages.index',
                        ['subdomain' => $website->subdomain]
                    ) }}"
                    class="rounded-xl border border-slate-300 px-5 py-3 text-sm font-bold"
                >
                    Cancel
                </a>


                <button
                    type="submit"
                    class="rounded-xl bg-blue-600 px-6 py-3 text-sm font-black text-white"
                >
                    {{ $page
                        ? 'Save Page'
                        : 'Create Page' }}
                </button>

            </div>

        </form>

    </div>

</div>

</body>
</html>
