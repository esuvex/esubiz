<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>{{ $website->name }}</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            font-family:
                Inter,
                ui-sans-serif,
                system-ui,
                -apple-system,
                BlinkMacSystemFont,
                "Segoe UI",
                sans-serif;
            background: #f8fafc;
            color: #0f172a;
        }

        .page {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 20px;
        }

        .card {
            width: 100%;
            max-width: 760px;
            padding: 64px 40px;
            text-align: center;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 28px;
            box-shadow: 0 24px 70px rgba(15, 23, 42, 0.08);
        }

        .brand {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 60px;
            height: 60px;
            margin-bottom: 28px;
            border-radius: 18px;
            background: #0f172a;
            color: #ffffff;
            font-size: 25px;
            font-weight: 800;
            letter-spacing: -0.03em;
        }

        .eyebrow {
            margin-bottom: 12px;
            color: #2563eb;
            font-size: 13px;
            font-weight: 700;
            letter-spacing: 0.18em;
            text-transform: uppercase;
        }

        h1 {
            margin: 0;
            font-size: clamp(34px, 6vw, 56px);
            line-height: 1.08;
            letter-spacing: -0.04em;
        }

        .message {
            margin: 22px auto 0;
            max-width: 580px;
            color: #64748b;
            font-size: 18px;
            line-height: 1.7;
        }

        .address-label {
            margin-top: 34px;
            color: #94a3b8;
            font-size: 13px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.08em;
        }

        .address {
            margin-top: 8px;
            color: #0f172a;
            font-size: 17px;
            font-weight: 700;
            word-break: break-word;
        }

        .website-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-top: 30px;
            padding: 14px 24px;
            border-radius: 12px;
            background: #2563eb;
            color: #ffffff;
            text-decoration: none;
            font-weight: 700;
            transition: background 0.2s ease;
        }

        .website-link:hover {
            background: #1d4ed8;
        }

        .powered {
            margin-top: 42px;
            color: #94a3b8;
            font-size: 13px;
        }
    </style>
</head>

<body>

<div class="page">

    <main class="card">

        <div class="brand">
            E
        </div>

        <div class="eyebrow">
            Esubiz Website
        </div>

        <h1>
            {{ $website->name }}
        </h1>

        <p class="message">
            Welcome to {{ $website->name }}.
            This website is currently using the default Esubiz website page
            while its website experience is being configured.
        </p>

        <div class="address-label">
            Website Address
        </div>

        <div class="address">
            {{ $websiteUrl }}
        </div>

        <a
            href="{{ $websiteUrl }}"
            class="website-link"
        >
            Visit Website
        </a>

        <p class="powered">
            Powered by Esubiz
        </p>

    </main>

</div>

</body>
</html>
