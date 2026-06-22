<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>500 — Server Error</title>
    @vite('resources/css/app.css')
    <style>
        .error-page {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--background-color, #f8fafc);
            font-family: system-ui, -apple-system, sans-serif;
        }
        .error-card {
            text-align: center;
            padding: 48px;
            max-width: 420px;
        }
        .error-code {
            font-size: 72px;
            font-weight: 900;
            color: var(--accent-color, #e74c3c);
            line-height: 1;
            margin-bottom: 8px;
        }
        .error-title {
            font-size: 20px;
            font-weight: 700;
            color: var(--text-color, #0f172a);
            margin-bottom: 8px;
        }
        .error-desc {
            font-size: 14px;
            color: var(--muted, #64748b);
            margin-bottom: 24px;
            line-height: 1.5;
        }
        .error-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 24px;
            background: var(--accent-color, #6c5ce7);
            color: #fff;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s ease;
            cursor: pointer;
        }
        .error-btn:hover {
            opacity: 0.9;
            transform: translateY(-1px);
        }
    </style>
</head>
<body>
    <div class="error-page">
        <div class="error-card">
            <div class="error-code">500</div>
            <div class="error-title">Server Error</div>
            <p class="error-desc">Oops, something went wrong on our servers.</p>
            <a href="{{ route('welcome') }}" class="error-btn">
                <i class="fa-solid fa-house"></i> Go to Home
            </a>
        </div>
    </div>
</body>
</html>
