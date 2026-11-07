<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    {{-- عنوان، توضیح، canonical، robots، Open Graph، Twitter و JSON-LD (پکیج laravel-seo) --}}
    {!! App\Support\Seo::render($title ?? null) !!}
    <link rel="icon" href="/favicon.ico" sizes="32x32">
    <link rel="apple-touch-icon" href="/apple-touch-icon.png">
    <link rel="manifest" href="/site.webmanifest">
    <meta name="theme-color" content="#7c3aed">

    {{-- حالت تیره قبل از نمایش صفحه اعمال می‌شود تا صفحه چشمک نزند --}}
    <script>
        try {
            const theme = localStorage.getItem('neshooni-theme');
            if (theme === 'dark' || (!theme && matchMedia('(prefers-color-scheme: dark)').matches)) {
                document.documentElement.classList.add('dark');
            }
        } catch (e) {}
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-screen flex-col bg-page font-sans text-slate-800 antialiased">
    {{-- نوار پیشرفت اسکرول (app.css → scroll-progress) --}}
    <div class="scroll-progress" aria-hidden="true"></div>

    {{-- مدیر در حالت تعمیر سایت را عادی می‌بیند؛ این نوار یادآوری می‌کند که برای بقیه بسته است --}}
    @if (auth()->user()?->isAdmin() && App\Support\Maintenance::isActive())
        <div class="bg-amber-400 px-4 py-2 text-center text-sm font-semibold text-amber-950">
            🛠️ سایت در حالت تعمیر است و بازدیدکننده‌ها آن را نمی‌بینند.
            @can('site.maintenance')
                <a href="{{ route('admin.maintenance') }}" wire:navigate class="cursor-pointer underline">مدیریت</a>
            @endcan
        </div>
    @endif

    @include('partials.header')

    <main class="flex-1">
        {{ $slot }}
    </main>

    @include('partials.footer')

    {{-- دکمه بازگشت به بالا؛ بعد از کمی اسکرول ظاهر می‌شود --}}
    <button x-data="{ show: false }" @scroll.window.throttle.150ms="show = scrollY > 600" x-show="show" x-cloak
            x-transition:enter="transition duration-300 ease-out" x-transition:enter-start="translate-y-4 scale-75 opacity-0"
            x-transition:leave="transition duration-200 ease-in" x-transition:leave-end="translate-y-4 scale-75 opacity-0"
            @click="scrollTo({ top: 0, behavior: 'smooth' })"
            class="fixed bottom-5 left-5 z-50 grid size-11 cursor-pointer place-items-center rounded-full bg-violet-600 text-white shadow-lg shadow-violet-500/30 transition hover:-translate-y-1 hover:bg-violet-700"
            aria-label="بازگشت به بالای صفحه">
        <svg class="size-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M12 19V5M5 12l7-7 7 7"/></svg>
    </button>

    {{-- مودال پیشنهاد خبرنامه بر اساس علاقه‌مندی‌ها --}}
    <livewire:newsletter-modal />

    {{-- پیام‌های کوتاه؛ از PHP با $this->dispatch('toast', message: '...') یا session('toast') --}}
    <div x-data="toasts" @toast.window="add($event.detail)"
         @if (session('toast')) x-init="add(@js(session('toast')))" @endif
         class="pointer-events-none fixed inset-x-0 top-20 z-[90] flex flex-col items-center gap-2 px-4">
        <template x-for="t in items" :key="t.id">
            <div :class="{ 'bg-emerald-600': t.type === 'success', 'bg-rose-600': t.type === 'error', 'bg-gray-800': t.type === 'info' }"
                 class="toast-enter pointer-events-auto max-w-md rounded-xl px-4 py-3 text-sm text-white shadow-lg" x-text="t.message"
                 x-transition:leave="transition duration-300 ease-in" x-transition:leave-end="-translate-y-3 scale-95 opacity-0"></div>
        </template>
    </div>
</body>
</html>
