<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Star Atlas Core</title>
    <style>
        :root {
            color-scheme: light dark;
            font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        }

        body {
            align-items: center;
            background: #f7f8fa;
            color: #15191f;
            display: flex;
            min-height: 100vh;
            margin: 0;
            padding: 24px;
        }

        main {
            max-width: 720px;
        }

        h1 {
            font-size: clamp(2rem, 6vw, 4.25rem);
            font-weight: 700;
            letter-spacing: 0;
            line-height: 1;
            margin: 0 0 16px;
        }

        p {
            font-size: 1.125rem;
            line-height: 1.6;
            margin: 0;
        }

        @media (prefers-color-scheme: dark) {
            body {
                background: #101317;
                color: #f5f7fa;
            }
        }
    </style>
</head>
<body>
    <main>
        <h1>Star Atlas Core</h1>
        <p>Central backend for Star Atlas Media. Public APIs, SDK assets, SSO endpoints, and the protected Core Admin panel are bootstrapped here.</p>
    </main>
</body>
</html>
