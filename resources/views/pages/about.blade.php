{{-- داده سئو قبل از قالب تنظیم می‌شود (محتوای صفحه پیش از قالب رندر می‌شود) --}}
@php
    App\Support\Seo::set(new RalphJSmit\Laravel\SEO\Support\SEOData(
        title: 'درباره نشونی',
        description: 'نشونی ویترین پیج‌ها، کانال‌ها و سایت‌های فروشگاهی کسب‌وکارهای ایرانی است؛ با بررسی دستی، نشان تأیید کسب‌وکار و نظر واقعی خریداران. ثبت پیج و سایت فروشگاهی رایگان است.',
        type: 'website',
    ));
@endphp

<x-layouts::app title="درباره ما">
    <div class="mx-auto max-w-4xl px-4 py-12">
        <div class="text-center">
            <span class="mx-auto grid size-16 place-items-center rounded-2xl bg-linear-to-br from-violet-600 to-fuchsia-500 text-3xl font-black text-white">ن</span>
            <h1 class="mt-5 text-3xl font-black">درباره نشونی</h1>
            <p class="mx-auto mt-4 max-w-2xl leading-8 text-slate-600">
                خیلی از کسب‌وکارهای ایرانی فروشگاه‌شان را در اینستاگرام، تلگرام، ایتا، روبیکا و بله دارند؛
                اما پیدا کردن پیج مطمئن در میان این‌همه پیام‌رسان کار سختی است.
                نشونی یک ویترین ساده است که این پیج‌ها را، همراه با سایت‌های فروشگاهی کسب‌وکارها، دسته‌بندی‌شده و با امتیاز و نظر کاربران یک‌جا نشان می‌دهد.
            </p>
        </div>

        <div class="mt-12 grid gap-4 sm:grid-cols-3">
            @foreach ([['🛡️', 'بررسی دستی', 'هر پیج، سایت فروشگاهی، عکس و نظر قبل از انتشار توسط تیم ما بررسی می‌شود.'], ['✅', 'نشان تأیید', 'کسب‌وکارهایی که مدارک خود را ارائه کرده‌اند تیک آبی دارند.'], ['💬', 'نظر واقعی', 'خریداران تجربه‌شان را ثبت می‌کنند و صاحب پیج پاسخ می‌دهد.']] as [$icon, $title, $text])
                <div class="card p-6 text-center">
                    <div class="text-3xl">{{ $icon }}</div>
                    <h2 class="mt-3 font-bold">{{ $title }}</h2>
                    <p class="mt-2 text-sm leading-7 text-slate-500">{{ $text }}</p>
                </div>
            @endforeach
        </div>

        <div class="card mt-10 p-8 text-center">
            <h2 class="text-xl font-extrabold">برای کسب‌وکارها</h2>
            <p class="mt-2 leading-8 text-slate-600">ثبت پیج و سایت فروشگاهی رایگان است. برای دیده شدن بیشتر می‌توانید پیج یا سایت خود را «ویژه» کنید یا نشان تأیید بگیرید.</p>
            <div class="mt-6 flex flex-wrap justify-center gap-2">
                <a href="{{ route('register') }}" wire:navigate class="cursor-pointer btn-primary">ثبت رایگان پیج</a>
                <a href="{{ auth()->check() ? route('dashboard.websites.create') : route('register', ['intent' => 'website']) }}" wire:navigate class="cursor-pointer btn bg-teal-600 text-white hover:bg-teal-700">🌐 ثبت سایت فروشگاهی</a>
                <a href="{{ route('contact') }}" wire:navigate class="cursor-pointer btn-light">تماس با ما</a>
            </div>
        </div>
    </div>
</x-layouts::app>
