{{-- هدر سایت. منوهای کشویی با Alpine (فقط باز و بسته شدن) --}}
<header class="sticky top-0 z-40 border-b border-slate-200 bg-surface/90 backdrop-blur" x-data="{ mobile: false }">
    <div class="mx-auto flex h-16 max-w-7xl items-center gap-3 px-4">
        <a href="{{ route('home') }}" wire:navigate class="cursor-pointer shrink-0" aria-label="{{ config('site.name') }}">
            <x-logo />
        </a>

        <nav class="ms-4 hidden items-center gap-1 text-sm md:flex">
            @foreach (['home' => 'خانه', 'explore' => 'همه پیج‌ها', 'about' => 'درباره ما', 'contact' => 'تماس با ما'] as $route => $label)
                <a href="{{ route($route) }}" wire:navigate @class(['cursor-pointer rounded-lg px-3 py-2', 'bg-violet-50 font-semibold text-violet-700' => request()->routeIs($route), 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' => ! request()->routeIs($route)])>{{ $label }}</a>
            @endforeach
        </nav>

        <div class="flex-1"></div>

        @guest
            <div class="hidden items-center gap-2 sm:flex">
                <a href="{{ route('login') }}" wire:navigate class="cursor-pointer btn text-slate-600 hover:bg-slate-100">ورود</a>
                <a href="{{ route('register') }}" wire:navigate class="cursor-pointer btn-primary">+ ثبت رایگان پیج</a>
            </div>
        @endguest

        @auth
            <a href="{{ route('dashboard.listings.create') }}" wire:navigate class="cursor-pointer btn-primary hidden sm:inline-flex">+ ثبت پیج</a>
            <livewire:notification-bell />

            <div class="relative" x-data="{ open: false }" @click.outside="open = false">
                <button @click="open = !open" class="cursor-pointer flex items-center gap-2 rounded-full p-1 pe-2 hover:bg-slate-100">
                    <x-avatar :src="auth()->user()->avatarUrl()" :name="auth()->user()->name" :seed="auth()->id()" class="size-8 text-sm" rounded="rounded-full" />
                    <span class="hidden max-w-32 truncate text-sm font-medium sm:block">{{ auth()->user()->name }}</span>
                </button>
                <div x-show="open" x-transition x-cloak class="absolute left-0 mt-2 w-56 rounded-xl border border-slate-200 bg-surface p-1.5 text-sm shadow-xl">
                    <div class="border-b border-slate-100 px-3 py-2">
                        <div class="truncate font-semibold">{{ auth()->user()->name }}</div>
                        <div class="text-xs text-slate-500" dir="ltr" style="text-align:right">{{ auth()->user()->mobile }}</div>
                    </div>
                    @can('access-panel')
                        <a href="{{ route('admin.dashboard') }}" wire:navigate class="cursor-pointer side-link">🛡️ پنل {{ auth()->user()->isAdmin() ? 'مدیریت' : auth()->user()->roleLabel() }}</a>
                    @endcan
                    <a href="{{ route('dashboard') }}" wire:navigate class="cursor-pointer side-link">📋 پیج‌های من</a>
                    <a href="{{ route('dashboard.payments') }}" wire:navigate class="cursor-pointer side-link">💳 پرداخت‌های من</a>
                    <a href="{{ route('dashboard.profile') }}" wire:navigate class="cursor-pointer side-link">👤 ویرایش پروفایل</a>
                    <a href="{{ route('business.show', auth()->user()) }}" wire:navigate class="cursor-pointer side-link">🏪 صفحه عمومی من</a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="cursor-pointer side-link w-full text-rose-600 hover:bg-rose-50 hover:text-rose-700">↩️ خروج</button>
                    </form>
                </div>
            </div>
        @endauth

        <button onclick="toggleTheme()" class="cursor-pointer rounded-lg p-2 text-slate-600 hover:bg-slate-100" aria-label="تغییر حالت روشن و تیره">
            <svg class="size-5 dark:hidden" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 12.8A9 9 0 1 1 11.2 3a7 7 0 0 0 9.8 9.8z"/></svg>
            <svg class="hidden size-5 dark:block" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M2 12h2M20 12h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4"/></svg>
        </button>
        <button @click="mobile = !mobile" class="cursor-pointer rounded-lg p-2 text-slate-600 hover:bg-slate-100 md:hidden" aria-label="منو">
            <svg class="size-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
        </button>
    </div>

    {{-- منوی موبایل --}}
    <div x-show="mobile" x-transition x-cloak class="border-t border-slate-100 bg-surface px-4 py-3 md:hidden">
        <a href="{{ route('home') }}" wire:navigate class="cursor-pointer side-link">خانه</a>
        <a href="{{ route('explore') }}" wire:navigate class="cursor-pointer side-link">همه پیج‌ها</a>
        <a href="{{ route('about') }}" wire:navigate class="cursor-pointer side-link">درباره ما</a>
        <a href="{{ route('contact') }}" wire:navigate class="cursor-pointer side-link">تماس با ما</a>
        @guest
            <div class="mt-2 grid grid-cols-2 gap-2">
                <a href="{{ route('login') }}" wire:navigate class="cursor-pointer btn-light">ورود</a>
                <a href="{{ route('register') }}" wire:navigate class="cursor-pointer btn-primary">ثبت‌نام</a>
            </div>
        @else
            <a href="{{ route('dashboard.listings.create') }}" wire:navigate class="cursor-pointer side-link">➕ ثبت پیج جدید</a>
            <a href="{{ route('dashboard') }}" wire:navigate class="cursor-pointer side-link">📋 داشبورد</a>
        @endguest
    </div>
</header>
