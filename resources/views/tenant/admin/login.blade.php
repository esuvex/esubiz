<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Admin Login - {{ $website->name }}
    </title>

    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-slate-100">

<div
    class="flex min-h-screen items-center justify-center px-5 py-10"
>

    <div
        class="w-full max-w-md rounded-3xl border border-slate-200 bg-white p-8 shadow-xl"
    >

        <div
            class="text-xs font-black uppercase tracking-[0.18em] text-blue-600"
        >
            Esubiz Core CMS
        </div>

        <h1
            class="mt-3 text-3xl font-black tracking-tight text-slate-900"
        >
            {{ $website->name }}
        </h1>

        <p
            class="mt-2 text-sm leading-6 text-slate-500"
        >
            Sign in to manage this website.
        </p>


        <div
            class="mt-8 rounded-2xl border border-blue-100 bg-blue-50 p-4 text-sm text-blue-800"
        >
            Tenant administrator authentication is being connected next.
            This page is now independent from the central Esubiz Admin login.
        </div>


        <div class="mt-7 space-y-3">

            <button
                type="button"
                disabled
                class="w-full cursor-not-allowed rounded-xl bg-slate-900 px-5 py-3.5 font-black text-white opacity-60"
            >
                Website Account Login — Coming Next
            </button>

            @if($ssoUrl)

                <a
                    href="https://esubiz.com/websites/{{ $website->id }}/dashboard"
                    class="block w-full rounded-xl border border-blue-200 bg-blue-50 px-5 py-3.5 text-center font-black text-blue-700 transition hover:bg-blue-100"
                >
                    Continue with Esubiz
                </a>

            @else

                <button
                    type="button"
                    disabled
                    class="w-full cursor-not-allowed rounded-xl border border-slate-300 bg-white px-5 py-3.5 font-black text-slate-400 opacity-70"
                >
                    Esubiz SSO Not Configured
                </button>

            @endif

        </div>


        <a
            href="/"
            class="mt-7 block text-center text-sm font-bold text-blue-600"
        >
            ← Back to Website
        </a>

    </div>

</div>

</body>
</html>
