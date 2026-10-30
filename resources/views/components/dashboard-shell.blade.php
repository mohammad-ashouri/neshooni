{{--
    قاب صفحه‌های داشبورد کاربر: منوی کناری + محتوای صفحه.
    استفاده: <x-dashboard-shell title="پیج‌های من"> ... </x-dashboard-shell>
--}}
@props(['title', 'actions' => null])

@php
    $menu = [
        ['dashboard', '📋', 'پیج‌های من'],
        ['dashboard.stats', '📊', 'آمار بازدید'],
        ['dashboard.seo', '🔎', 'سئوی پیج'],
        ['dashboard.reviews', '💬', 'نظرات پیج‌ها'],
        ['dashboard.following', '❤️', 'دنبال‌شده‌ها'],
        ['dashboard.alerts', '🔔', 'اعلان و خبرنامه'],
        ['dashboard.banner', '🖼️', 'بنر و نشان سایت'],
        ['dashboard.verification', '✅', 'نشان تأیید'],
        ['dashboard.payments', '💳', 'پرداخت‌ها'],
        ['dashboard.profile', '👤', 'پروفایل'],
    ];
@endphp

<div class="mx-auto grid max-w-7xl gap-6 px-4 py-8 lg:grid-cols-[15rem_1fr]">
    <aside class="h-fit min-w-0 lg:sticky lg:top-20">
        <div class="card mb-3 flex items-center gap-3 p-4">
            <x-avatar :src="auth()->user()->avatarUrl()" :name="auth()->user()->name" :seed="auth()->id()" class="size-11 text-lg" rounded="rounded-full" />
            <div class="min-w-0">
                <div class="flex items-center gap-1 truncate font-bold">{{ auth()->user()->name }} @if (auth()->user()->isVerified()) <x-verified-badge /> @endif</div>
                <a href="{{ route('business.show', auth()->user()) }}" wire:navigate class="cursor-pointer text-xs text-violet-700 hover:underline">صفحه عمومی من</a>
            </div>
        </div>
        <nav class="card flex gap-1 overflow-x-auto p-2 lg:flex-col">
            {{-- wire:current لینک صفحه فعلی را در مرورگر رنگی می‌کند (بعد از به‌روزرسانی‌های Livewire هم درست می‌ماند) --}}
            @foreach ($menu as [$route, $icon, $label])
                <a href="{{ route($route) }}" wire:navigate @if ($route === 'dashboard') wire:current.exact="side-link-active" @else wire:current="side-link-active" @endif class="cursor-pointer side-link shrink-0">
                    <span>{{ $icon }}</span> {{ $label }}
                </a>
            @endforeach
            <a href="{{ route('help') }}" wire:navigate class="cursor-pointer side-link shrink-0"><span>❓</span> راهنما</a>
        </nav>
    </aside>

    <section class="min-w-0">
        <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
            <h1 class="text-2xl font-extrabold">{{ $title }}</h1>
            {{ $actions }}
        </div>
        {{ $slot }}
    </section>
</div>
