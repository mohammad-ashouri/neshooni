{{--
    قالب ساده صفحه‌های خطا. عمداً به Livewire و دیتابیس وابسته نیست
    تا اگر خطا از همان‌ها باشد، این صفحه باز هم نمایش داده شود.
--}}
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, follow">
    <title>@yield('title') | {{ config('site.name') }}</title>
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    <script>
        try {
            const theme = localStorage.getItem('neshooni-theme');
            if (theme === 'dark' || (!theme && matchMedia('(prefers-color-scheme: dark)').matches)) document.documentElement.classList.add('dark');
        } catch (e) {}
    </script>
    @vite('resources/css/app.css')
</head>
<body class="grid min-h-screen place-items-center bg-page px-4 font-sans text-slate-800">
    <main class="max-w-md text-center">
        <a href="/" class="cursor-pointer mx-auto grid size-14 place-items-center rounded-2xl bg-linear-to-br from-violet-600 to-fuchsia-500 text-2xl font-black text-white">ن</a>
        <div class="mt-6 text-6xl font-black text-violet-600">@yield('code')</div>
        <h1 class="mt-3 text-xl font-extrabold">@yield('title')</h1>
        <p class="mt-2 leading-8 text-slate-500">@yield('message')</p>
        <div class="mt-6 flex flex-wrap justify-center gap-2">
            <a href="/" class="cursor-pointer btn-primary">صفحه اصلی</a>
            <a href="/c" class="cursor-pointer btn-light">همه پیج‌ها</a>
        </div>
    </main>
</body>
</html>
