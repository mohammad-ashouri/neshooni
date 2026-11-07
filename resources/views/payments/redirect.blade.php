{{-- صفحه انتقال خودکار به درگاه بانک (فرمی که درگاه خواسته با جاوااسکریپت ارسال می‌شود) --}}
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>انتقال به درگاه پرداخت</title>
    <style>
        :root { color-scheme: light dark; --bg: #f8fafc; --card: #fff; --text: #1e293b; --muted: #64748b; --accent: #059669; }
        @media (prefers-color-scheme: dark) { :root { --bg: #0f172a; --card: #1e293b; --text: #e2e8f0; --muted: #94a3b8; --accent: #10b981; } }
        body { margin: 0; min-height: 100vh; display: grid; place-items: center; background: var(--bg); color: var(--text); font-family: Vazirmatn, Tahoma, sans-serif; }
        .card { background: var(--card); border-radius: 20px; padding: 32px 28px; max-width: 360px; margin: 16px; text-align: center; box-shadow: 0 10px 30px rgb(0 0 0 / .08); }
        .spinner { width: 42px; height: 42px; margin: 0 auto 18px; border: 4px solid rgb(16 185 129 / .2); border-top-color: var(--accent); border-radius: 50%; animation: spin .8s linear infinite; }
        @keyframes spin { to { transform: rotate(360deg); } }
        h1 { font-size: 17px; margin: 0 0 8px; }
        p { color: var(--muted); font-size: 13px; line-height: 1.9; margin: 0 0 18px; }
        button { font: inherit; border: 0; border-radius: 12px; padding: 10px 22px; background: var(--accent); color: #fff; cursor: pointer; }
    </style>
</head>
<body>
    <div class="card">
        <div class="spinner"></div>
        <h1>در حال انتقال به درگاه {{ $gateway }}</h1>
        <p>اگر تا چند ثانیه دیگر منتقل نشدید، روی دکمه زیر بزنید.</p>
        <form id="gateway-form" method="{{ $method }}" action="{{ $action }}">
            @foreach ($inputs as $name => $value)
                <input type="hidden" name="{{ $name }}" value="{{ $value }}">
            @endforeach
            <button type="submit">رفتن به درگاه</button>
        </form>
    </div>
    <script>document.getElementById('gateway-form').submit();</script>
</body>
</html>
