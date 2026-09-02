<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta
        name="viewport"
        content="width=device-width, initial-scale=1"
    >

    <title>Page Not Found</title>

    <style>
        * {
            box-sizing: border-box;
        }

        html,
        body {
            margin: 0;
            min-height: 100%;
        }

        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            background:
                radial-gradient(
                    circle at top,
                    #eef3ff 0,
                    #f7f9fc 38%,
                    #ffffff 100%
                );
            color: #111827;
            font-family:
                Inter,
                ui-sans-serif,
                system-ui,
                -apple-system,
                BlinkMacSystemFont,
                "Segoe UI",
                sans-serif;
        }

        .error-card {
            width: 100%;
            max-width: 620px;
            padding: 54px 42px;
            text-align: center;
            background: rgba(255, 255, 255, .96);
            border: 1px solid #e5e7eb;
            border-radius: 24px;
            box-shadow:
                0 24px 70px rgba(17, 24, 39, .10);
        }

        .error-code {
            margin: 0 0 12px;
            font-size: clamp(64px, 13vw, 112px);
            line-height: .95;
            font-weight: 800;
            letter-spacing: -5px;
            color: #111827;
        }

        h1 {
            margin: 0 0 14px;
            font-size: clamp(26px, 5vw, 36px);
            line-height: 1.2;
        }

        p {
            max-width: 460px;
            margin: 0 auto 30px;
            color: #6b7280;
            font-size: 16px;
            line-height: 1.7;
        }

        .home-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 48px;
            padding: 0 24px;
            border-radius: 12px;
            background: #111827;
            color: #ffffff;
            font-size: 15px;
            font-weight: 700;
            text-decoration: none;
            transition:
                transform .15s ease,
                opacity .15s ease;
        }

        .home-button:hover {
            opacity: .92;
            transform: translateY(-1px);
        }

        @media (max-width: 575px) {
            .error-card {
                padding: 42px 22px;
                border-radius: 18px;
            }
        }
    </style>
</head>

<body>
    <main class="error-card">
        <div class="error-code">404</div>

        <h1>Page not found</h1>

        <p>
            The page you are looking for may have been moved,
            removed, or the address may be incorrect.
        </p>

        <a href="/" class="home-button">
            Back to Home
        </a>
    </main>
</body>
</html>
