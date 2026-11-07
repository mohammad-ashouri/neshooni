{{--
    تبلیغ «ثبت سایت فروشگاهی» در جای‌جای سایت.
    variant: banner (بخش پهن صفحه اول و فهرست‌ها) | card (ستون کناری) | inline (یک خط متن با لینک)
    اگر مدیر پلتفرم سایت فروشگاهی را غیرفعال کرده باشد، چیزی نمایش داده نمی‌شود.
--}}
@props(['variant' => 'banner'])

@php
    $enabled = App\Models\Platform::website() !== null;
    $url = auth()->check() ? route('dashboard.websites.create') : route('register', ['intent' => 'website']);
@endphp

@if ($enabled)
    @if ($variant === 'banner')
        <section {{ $attributes->class('relative overflow-hidden rounded-3xl border border-teal-200 bg-linear-to-l from-teal-50 to-emerald-50 p-6 sm:p-10 dark:border-teal-900 dark:from-teal-950/40 dark:to-emerald-950/40') }}>
            <div class="pointer-events-none absolute -top-16 -left-16 size-64 rounded-full bg-teal-300/30 blur-3xl"></div>
            <div class="relative grid items-center gap-8 md:grid-cols-[1fr_auto]">
                <div>
                    <span class="chip bg-teal-600 text-white">🌐 تازه در نشونی</span>
                    <h2 class="mt-4 text-2xl font-black text-slate-900">سایت فروشگاهی دارید؟ آن را هم معرفی کنید</h2>
                    <p class="mt-2 max-w-2xl leading-8 text-slate-600">
                        فروشگاه اینترنتی‌تان را مثل یک پیج در نشونی ثبت کنید تا در دسته و شهر خودش، کنار پیج‌ها دیده شود و مشتری مستقیم به سایت شما برود.
                    </p>
                    <ul class="mt-4 grid gap-2 text-sm text-slate-700 sm:grid-cols-2">
                        @foreach (['ثبت رایگان با آدرس سایت', 'نظر و امتیاز واقعی خریداران', 'آمار بازدید و کلیک ورود به سایت', 'صفحه جدا و قابل ایندکس در گوگل'] as $item)
                            <li class="flex items-center gap-2"><span class="text-teal-600">✓</span> {{ $item }}</li>
                        @endforeach
                    </ul>
                </div>
                <div class="flex flex-col items-stretch gap-2 text-center">
                    <a href="{{ $url }}" wire:navigate class="cursor-pointer btn bg-teal-600 px-8 text-white shadow-lg shadow-teal-600/20 hover:-translate-y-0.5 hover:bg-teal-700">🌐 ثبت رایگان سایت فروشگاهی</a>
                    <a href="{{ route('help') }}" wire:navigate class="cursor-pointer text-xs text-teal-700 dark:text-teal-400 hover:underline">راهنمای ثبت سایت</a>
                </div>
            </div>
        </section>
    @elseif ($variant === 'card')
        <a href="{{ $url }}" wire:navigate {{ $attributes->class('cursor-pointer card block border-teal-200 bg-teal-50/60 p-5 transition duration-300 hover:-translate-y-1 hover:border-teal-300 hover:shadow-lg hover:shadow-teal-500/10 dark:border-teal-900 dark:bg-teal-950/30') }}>
            <div class="text-2xl">🌐</div>
            <h2 class="mt-2 font-bold">سایت فروشگاهی دارید؟</h2>
            <p class="mt-1 text-xs leading-6 text-slate-600">آن را رایگان در نشونی ثبت کنید تا خریداران پیدایش کنند و درباره‌اش نظر بدهند.</p>
            <span class="mt-3 inline-block text-sm font-semibold text-teal-700 dark:text-teal-400">ثبت سایت فروشگاهی ←</span>
        </a>
    @else
        <p {{ $attributes->class('text-sm text-slate-500') }}>
            🌐 سایت فروشگاهی دارید؟ <a href="{{ $url }}" wire:navigate class="cursor-pointer font-semibold text-teal-700 dark:text-teal-400 hover:underline">آن را هم رایگان ثبت کنید</a>
        </p>
    @endif
@endif
